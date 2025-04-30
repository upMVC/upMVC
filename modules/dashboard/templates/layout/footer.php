</main>
        <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']): ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300 translate-y-full">
        <span id="toastMessage"></span>
    </div>

    <script>
    function showToast(message, duration = 3000) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        toast.classList.remove('translate-y-full');
        
        setTimeout(() => {
            toast.classList.add('translate-y-full');
        }, duration);
    }

    // Show toast if there's a message in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        showToast(decodeURIComponent(message));
    }
    </script>
</body>
</html>
