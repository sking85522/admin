            </div>
        </main>

        <?php if (Auth::check()): ?>
            <footer class="bg-white text-center text-sm p-4 shadow mt-auto flex flex-col md:flex-row justify-between items-center text-gray-500">
                <div>
                    Powered by <a href="<?= PANEL_URL ?>" target="_blank" class="font-bold text-primary hover:underline"><?= PANEL_NAME ?></a>
                </div>
                <div class="mt-2 md:mt-0">
                    &copy; <?= date('Y') ?> <a href="<?= COMPANY_URL ?>" target="_blank" class="font-bold text-gray-700 hover:text-primary transition-colors"><?= COMPANY_NAME ?></a>. All rights reserved.
                </div>
            </footer>
        <?php endif; ?>
    </div>

    <!-- Alpine.js for some simple interactions if needed -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
