<?php $root = $root ?? ''; ?>
<footer class="site-footer">

    <div class="footer-inner">

        <div class="footer-brand">
            <strong>ASBookStore</strong>
            <p>Your Books, Your Stories.</p>
        </div>

        <nav class="footer-links" aria-label="Footer">
            <a href="<?= $root ?>index.php">Home</a>
            <a href="<?= $root ?>daftarbuku.php">Buku</a>
            <a href="<?= $root ?>about.php">About</a>
            <a href="<?= $root ?>contact.php">Contact</a>
        </nav>

    </div>

    <div class="footer-copy">
        &copy; <?= date("Y") ?> ASBookStore. Semua hak dilindungi.
    </div>

</footer>
