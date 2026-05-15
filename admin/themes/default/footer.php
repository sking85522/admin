            </div>
        </main>

        <?php if (Auth::check()): ?>
            <footer class="bg-white text-center text-sm p-4 shadow mt-auto">
                &copy; <?= date('Y') ?> <?= h(APP_NAME) ?>. All rights reserved.
            </footer>
        <?php endif; ?>
    </div>

    <!-- Alpine.js for some simple interactions if needed -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
