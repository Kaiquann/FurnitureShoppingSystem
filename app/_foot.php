<?php if (!isAdmin()) : ?>
    <!-- Live Chat -->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function () {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/66eef97f4cbc4814f7dccacd/1i8ant6oj';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
<?php endif; ?>
</main>
<style>
    footer {
        background-color: #413c3c;
        color: #fff;
        text-align: center;
        font-family: Arial, sans-serif;
        width: 100%;
    }

    .footer-line {
        width: 100%;
        border-bottom: 1px solid #ccc;
    }


    @media (max-width: 768px) {
        .footer-container {
            flex-direction: column;
            align-items: center;
        }

        .footer-links {
            width: 100%;
            justify-content: center;
            margin-bottom: 20px;
        }
    }
</style>

<footer>
    <div class="footer-line"></div>
    <p style="font-size: 15px;color:white;margin:15px;">Online Shopping System by <b><?= COMPANY_NAME ?></b> &middot;
    </p>
</footer>

<script>
    $(() => {
        document.title = '<?php echo $_title ?? COMPANY_NAME; ?>';
    });
</script>

</body>

</html>