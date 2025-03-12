// ========== ANIMACIONES AVANZADAS ==========

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar animaciones generales
    initializeAnimations();
    
    // Inicializar animaciones específicas basadas en la página actual
    initializePageSpecificAnimations();
    
    // Detector de scroll para animaciones basadas en visibilidad
    window.addEventListener('scroll', handleScrollAnimations);
    
    // Efecto números crecientes
    initializeCounters();
    
    // Aplicar efecto hover a los elementos interactivos
    applyHoverEffects();
    
    // Confeti para acciones de éxito
    checkForSuccessMessages();
});

// Inicializar animaciones básicas para todas las páginas
function initializeAnimations() {
    // Agregar clases de animate.css a elementos específicos con delays escalonados
    const animateElements = [
        { selector: 'h2', classes: 'animate__animated animate__fadeInDown' },
        { selector: '.card', classes: 'animate__animated animate__fadeIn', baseDelay: 100 },
        { selector: '.btn-primary', classes: 'animate__animated animate__pulse', repeat: true },
        { selector: '.alert-success', classes: 'animate__animated animate__bounceIn' },
        { selector: '.badge', classes: 'animate__animated animate__fadeIn' }
    ];
    
    animateElements.forEach(item => {
        const elements = document.querySelectorAll(item.selector);
        elements.forEach((el, index) => {
            el.classList.add(...item.classes.split(' '));
            
            // Aplicar delays escalonados si se especifica
            if (item.baseDelay) {
                el.style.animationDelay = `${item.baseDelay * index}ms`;
            }
            
            // Configurar repetición si es necesario
            if (item.repeat) {
                el.style.animationIterationCount = 'infinite';
                el.style.animationDuration = '2s';
            }
        });
    });
    
    // Agregar efecto de ondas para botones
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// Inicializar animaciones específicas basadas en la URL actual
function initializePageSpecificAnimations() {
    const currentPath = window.location.pathname;
    
    // Página principal
    if (currentPath.includes('index.php') || currentPath.endsWith('/')) {
        // Animación adicional para el banner principal
        const mainTitle = document.querySelector('h2.text-dark-red');
        if (mainTitle) {
            mainTitle.classList.add('main-title');
        }
        
        // Animación para las tarjetas de características
        const featureCards = document.querySelectorAll('.col-md-4 .card');
        featureCards.forEach((card, index) => {
            card.classList.add('animate__animated', 'animate__fadeInUp');
            card.style.animationDelay = `${(index + 1) * 200}ms`;
        });
    }
    
    // Página de recompensas
    if (currentPath.includes('rewards.php')) {
        // Efecto especial para las tarjetas de recompensas
        const rewardCards = document.querySelectorAll('.reward-card');
        rewardCards.forEach((card, index) => {
            card.classList.add('animate__animated', 'animate__fadeInUp');
            card.style.animationDelay = `${index * 150}ms`;
            
            // Agregar efecto de brillo
            card.classList.add('highlight-container');
        });
        
        // Animación para el contador de puntos
        const pointsDisplay = document.querySelector('.user-points strong');
        if (pointsDisplay) {
            pointsDisplay.classList.add('points-counter');
            
            // Animar el conteo de puntos al cargar la página
            animatePointsCounter(pointsDisplay);
        }
    }
    
    // Página de ideas
    if (currentPath.includes('ideas.php')) {
        // Efecto para tabla de ideas con entrada escalonada
        const ideaRows = document.querySelectorAll('tbody tr');
        ideaRows.forEach((row, index) => {
            row.classList.add('animate__animated', 'animate__fadeInRight');
            row.style.animationDelay = `${index * 100}ms`;
        });
    }
    
    // Página de revisión
    if (currentPath.includes('review.php') || currentPath.includes('review_idea.php')) {
        // Efectos para pestañas de revisión
        const tabButtons = document.querySelectorAll('.nav-tabs .nav-link');
        tabButtons.forEach(tab => {
            tab.addEventListener('click', function() {
                // Animar el contenido del tab seleccionado
                const targetId = this.getAttribute('data-bs-target');
                const targetContent = document.querySelector(targetId);
                
                if (targetContent) {
                    targetContent.classList.add('animate__animated', 'animate__fadeIn');
                    setTimeout(() => {
                        targetContent.classList.remove('animate__animated', 'animate__fadeIn');
                    }, 500);
                }
            });
        });
    }
    
    // Página de enviar idea
    if (currentPath.includes('submit_idea.php')) {
        // Efecto de transición suave al escribir en los campos
        const formInputs = document.querySelectorAll('.form-control, .form-select');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.mb-3, .mb-4').classList.add('highlight-active');
            });
            
            input.addEventListener('blur', function() {
                this.closest('.mb-3, .mb-4').classList.remove('highlight-active');
            });
        });
    }
    
    // Página de aprobar ideas
    if (currentPath.includes('approve.php') || currentPath.includes('approve_idea.php')) {
        // Efecto pulsante para botones de aprobación
        const approveButtons = document.querySelectorAll('.btn-success');
        approveButtons.forEach(button => {
            button.classList.add('pulse');
        });
    }
}

// Controlar animaciones basadas en scroll
function handleScrollAnimations() {
    const elements = document.querySelectorAll('.scroll-animate');
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementPosition < windowHeight - 50) {
            const animation = element.dataset.animation || 'fadeIn';
            element.classList.add('animate__animated', `animate__${animation}`);
            element.classList.remove('scroll-animate');
        }
    });
}

// Inicializar contadores numéricos
function initializeCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target);
        const duration = parseInt(counter.dataset.duration) || 1000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.round(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    });
}

// Animar contador de puntos
function animatePointsCounter(element) {
    const finalValue = parseInt(element.textContent);
    const duration = 1500;
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsedTime = currentTime - startTime;
        const progress = Math.min(elapsedTime / duration, 1);
        const currentValue = Math.floor(progress * finalValue);
        
        element.textContent = currentValue;
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = finalValue;
        }
    }
    
    requestAnimationFrame(updateCounter);
}

// Aplicar efectos hover personalizados
function applyHoverEffects() {
    // Efecto para tarjetas (zoom suave)
    const cards = document.querySelectorAll('.card:not(.reward-card)');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Efecto para filas de tabla
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(144, 21, 28, 0.05)';
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'all 0.3s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = '';
        });
    });
}

// Generar efecto de confeti para mensajes de éxito
function checkForSuccessMessages() {
    const successMessages = document.querySelectorAll('.alert-success');
    
    if (successMessages.length > 0) {
        createConfetti();
    }
    
    // También verificar éxito en parámetros URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success') && urlParams.get('success') === 'true') {
        createConfetti();
    }
}

// Crear efecto de confeti
function createConfetti() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);
    
    // Crear piezas de confeti
    const colors = ['#90151C', '#f8d568', '#28a745', '#17a2b8', '#ffc107'];
    
    for (let i = 0; i < 100; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
        confetti.style.opacity = Math.random() + 0.5;
        confetti.style.width = (Math.random() * 8 + 5) + 'px';
        confetti.style.height = (Math.random() * 8 + 5) + 'px';
        confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
        
        container.appendChild(confetti);
    }
    
    // Eliminar después de completar la animación
    setTimeout(() => {
        container.remove();
    }, 5000);
}

// Función para aplicar efectos de texto flotante
function createFloatingText(element, text, className = 'floating-text-success') {
    const floatingText = document.createElement('span');
    floatingText.className = className;
    floatingText.textContent = text;
    
    // Posicionar el texto flotante
    const rect = element.getBoundingClientRect();
    floatingText.style.left = `${rect.left + rect.width/2}px`;
    floatingText.style.top = `${rect.top}px`;
    
    document.body.appendChild(floatingText);
    
    // Eliminar después de completar la animación
    setTimeout(() => {
        floatingText.remove();
    }, 1500);
}

// Agregar efecto de desplazamiento suave para todos los enlaces internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Detector de actividad del usuario para mostrar mensajes de motivación
let inactivityTimer;
function setupInactivityDetection() {
    const resetTimer = () => {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(showMotivationalMessage, 60000); // 1 minuto
    };
    
    // Eventos para resetear el temporizador
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetTimer, true);
    });
    
    resetTimer();
}

function showMotivationalMessage() {
    // Array de mensajes motivacionales
    const messages = [
        "¡Tus ideas pueden transformar la empresa!",
        "¿Tienes una nueva idea? ¡Compártela ahora!",
        "Cada punto cuenta para obtener grandes premios",
        "¡Las mejores ideas son recompensadas!"
    ];
    
    // Crear y mostrar el mensaje
    const messageContainer = document.createElement('div');
    messageContainer.className = 'motivational-message animate__animated animate__fadeIn';
    messageContainer.textContent = messages[Math.floor(Math.random() * messages.length)];
    
    document.body.appendChild(messageContainer);
    
    // Eliminar después de unos segundos
    setTimeout(() => {
        messageContainer.classList.replace('animate__fadeIn', 'animate__fadeOut');
        setTimeout(() => {
            messageContainer.remove();
        }, 1000);
    }, 5000);
}