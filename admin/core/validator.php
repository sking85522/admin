<?php
// admin/core/validator.php

class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $ruleString) {
            $value = isset($data[$field]) ? trim($data[$field]) : null;
            $fieldRules = explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && empty($value) && $value !== '0') {
                    $this->addError($field, ucfirst($field) . " is required.");
                }

                if (strpos($rule, 'min:') === 0) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $this->addError($field, ucfirst($field) . " must be at least $min characters.");
                    }
                }

                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Invalid email format.");
                }
            }
        }

        return empty($this->errors);
    }

    public function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }
}
