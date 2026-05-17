<?php
// admin/core/webhook.php

class Webhook {
    public static function fire($event, $payload = []) {
        $db = new JsonDB(CONTENT_PATH . '/webhooks.json');
        $webhooks = $db->getAll();

        $data = [
            'event' => $event,
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
            'payload' => $payload
        ];

        $jsonPayload = json_encode($data);

        foreach ($webhooks as $hook) {
            // Only fire if the webhook is subscribed to this event, or all events ('*')
            if (in_array($event, $hook['events']) || in_array('*', $hook['events'])) {
                self::send($hook['url'], $jsonPayload, $hook['secret'] ?? '');
            }
        }
    }

    private static function send($url, $jsonPayload, $secret) {
        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ];

        // Add signature if secret exists (for external verification)
        if (!empty($secret)) {
            $signature = hash_hmac('sha256', $jsonPayload, $secret);
            $headers[] = 'X-Admin-Signature: ' . $signature;
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Don't hang the script waiting for external servers

        // We use curl_exec but ignore the result. This is a "fire and forget" webhook.
        curl_exec($ch);
        curl_close($ch);
    }
}
