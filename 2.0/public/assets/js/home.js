function navigate(page) {
            // Simulação de navegação
            window.location.href = page;
        }   

        function logout() {
            if (confirm('Deseja realmente sair do sistema?')) {
                window.location.href = 'logout.php';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.menu-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });