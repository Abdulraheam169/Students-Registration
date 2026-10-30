    </div><!-- /.container -->
</main>

<!--  Footer  -->
<footer class="app-footer py-3 mt-auto">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <span class="small text-muted mb-0">
                    &copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.
                </span>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script src="<?= e(BASE_URL) ?>/assets/js/main.js"></script>
<?php if (!empty($page_scripts)): ?>
    <?php foreach ((array) $page_scripts as $script): ?>
        <script src="<?= e(BASE_URL . '/assets/js/' . $script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
