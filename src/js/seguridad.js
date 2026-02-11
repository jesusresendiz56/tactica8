// src/js/seguridad.js

(function() {
    'use strict';
    
    // ============================================
    // SEGURIDAD TÁCTICA 8 - PROTECCIÓN TOTAL
    // ============================================
    
    // 1. BLOQUEAR CLIC DERECHO
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        showAlert();
        return false;
    });
    
    // 2. BLOQUEAR TECLAS DE DESARROLLO
    document.addEventListener('keydown', function(e) {
        const blockedKeys = [
            'F12', 'F5', 'F11', 'F7', 'F3', 'Delete', 'Insert'
        ];
        
        const blockedCombos = [
            (e.ctrlKey && e.shiftKey && e.key === 'I'),
            (e.ctrlKey && e.shiftKey && e.key === 'J'),
            (e.ctrlKey && e.key === 'U'),
            (e.ctrlKey && e.key === 'u'),
            (e.ctrlKey && e.key === 's'),
            (e.ctrlKey && e.key === 'S'),
            (e.ctrlKey && e.shiftKey && e.key === 'C'),
            (e.ctrlKey && e.key === 'Shift'),
            (e.ctrlKey && e.key === 'p'),
            (e.ctrlKey && e.key === 'P'),
            (e.altKey && e.key === 'F4')
        ];
        
        if (blockedKeys.includes(e.key) || blockedCombos.includes(true)) {
            e.preventDefault();
            showAlert();
            return false;
        }
    });
    
    // 3. DETECTAR DEVTOOLS
    function detectDevTools() {
        const widthThreshold = window.outerWidth - window.innerWidth > 160;
        const heightThreshold = window.outerHeight - window.innerHeight > 160;
        
        if (widthThreshold || heightThreshold) {
            document.body.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: #1e3c72;
                    color: white;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    font-family: Arial, sans-serif;
                    z-index: 999999;
                ">
                    <h1 style="color: #ec1f27; font-size: 48px; margin-bottom: 20px;">⛔ ACCESO DENEGADO</h1>
                    <p style="font-size: 20px; margin-bottom: 10px;">No está permitido inspeccionar esta página</p>
                    <p style="font-size: 16px; opacity: 0.8;">TÁCTICA 8 - Sistema Privado</p>
                </div>
            `;
        }
    }
    
    // 4. BLOQUEAR SELECCIÓN DE TEXTO
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    // 5. BLOQUEAR ARRASTRAR
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    // 6. BLOQUEAR COPIAR, CORTAR Y PEGAR
    document.addEventListener('copy', function(e) {
        e.preventDefault();
        showAlert();
        return false;
    });
    
    document.addEventListener('cut', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('paste', function(e) {
        e.preventDefault();
        return false;
    });
    
    // 7. ALERTA PERSONALIZADA
    function showAlert() {
        const alertBox = document.createElement('div');
        alertBox.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: #ec1f27;
                color: white;
                padding: 15px 25px;
                border-radius: 5px;
                font-weight: bold;
                box-shadow: 0 4px 6px rgba(0,0,0,0.2);
                z-index: 99999;
                animation: slideIn 0.3s ease;
            ">
                ❌ Acción no permitida en este sistema
            </div>
            <style>
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            </style>
        `;
        document.body.appendChild(alertBox);
        
        setTimeout(function() {
            alertBox.remove();
        }, 3000);
    }
    
    // 8. DETECCIÓN CONTINUA DE DEVTOOLS
    setInterval(detectDevTools, 1000);
    
    // 9. BLOQUEAR CONSOLA
    setInterval(function() {
        if (typeof console.clear !== 'undefined') {
            console.clear = function() {};
        }
        if (typeof console.log !== 'undefined') {
            console.log = function() {};
        }
        if (typeof console.info !== 'undefined') {
            console.info = function() {};
        }
        if (typeof console.warn !== 'undefined') {
            console.warn = function() {};
        }
        if (typeof console.error !== 'undefined') {
            console.error = function() {};
        }
        if (typeof console.table !== 'undefined') {
            console.table = function() {};
        }
    }, 100);
    
    // 10. OFUSCAR HTML
    const style = document.createElement('style');
    style.innerHTML = `
        body {
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
        }
        
        img, a, .card, .menu, .header {
            pointer-events: none;
        }
        
        a, button, .exit-link, .exit-icon, .header-exit a, .menu a, input, select, .search-box input {
            pointer-events: auto !important;
        }
    `;
    document.head.appendChild(style);
    
    // 11. BLOQUEAR CAPTURA DE PANTALLA
    document.addEventListener('keyup', function(e) {
        if (e.key === 'PrintScreen') {
            navigator.clipboard.writeText('TÁCTICA 8 - Captura de pantalla bloqueada');
            showAlert();
        }
    });
    
})();