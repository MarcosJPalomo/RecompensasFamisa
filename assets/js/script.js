// JavaScript mejorado para el Programa de Recompensas

document.addEventListener('DOMContentLoaded', function() {
    // Confirmar canje de recompensa
    const redeemForms = document.querySelectorAll('.reward-card form');
    
    if (redeemForms.length > 0) {
        redeemForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const pointsRequired = this.closest('.reward-card').querySelector('.points-required strong').textContent;
                const rewardName = this.closest('.reward-card').querySelector('h4').textContent;
                
                if (!confirm(`¿Estás seguro de que deseas canjear "${rewardName}" por ${pointsRequired} puntos?`)) {
                    e.preventDefault();
                }
            });
        });
    }

    // Validación de formulario para enviar idea
    const ideaForm = document.querySelector('form#idea-form');
    
    if (ideaForm) {
        ideaForm.addEventListener('submit', function(e) {
            const title = document.querySelector('#title').value.trim();
            const description = document.querySelector('#description').value.trim();
            
            if (title.length < 5) {
                alert('El título debe tener al menos 5 caracteres');
                e.preventDefault();
                return false;
            }
            
            if (description.length < 20) {
                alert('La descripción debe tener al menos 20 caracteres');
                e.preventDefault();
                return false;
            }
        });
    }

    // Animaciones para elementos con la clase .animate__animated
    const animatedElements = document.querySelectorAll('.animate__animated');
    
    if (animatedElements.length > 0) {
        animatedElements.forEach(el => {
            // Aseguramos que las animaciones solo ocurran cuando el elemento sea visible
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__' + entry.target.dataset.animation);
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            observer.observe(el);
        });
    }

    // Cierre automático de alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert-success, .alert-info');
    
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('animate__fadeOut');
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    }

    // Añadir tooltips a los elementos con el atributo data-tooltip
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    if (tooltipElements.length > 0 && typeof bootstrap !== 'undefined') {
        tooltipElements.forEach(el => {
            new bootstrap.Tooltip(el);
        });
    }
});

// Función para confirmar acciones potencialmente destructivas
function confirmAction(message = '¿Estás seguro de que quieres realizar esta acción?') {
    return confirm(message);
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} animate__animated animate__fadeIn`;
    notification.innerHTML = message;
    
    const container = document.querySelector('.container');
    container.insertBefore(notification, container.firstChild);
    
    setTimeout(() => {
        notification.classList.remove('animate__fadeIn');
        notification.classList.add('animate__fadeOut');
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 5000);
}