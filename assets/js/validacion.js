/**
 * validacion.js — Validación del formulario en el CLIENTE.
 * Mejora la experiencia de usuario; la validación definitiva
 * siempre se repite en el servidor (Estudiante::validar).
 * @author Carlos Omar Galvez
 */
(function () {
    'use strict';

    const form = document.getElementById('form-registro');
    if (!form) return;

    /** Algoritmo módulo 10 de la cédula ecuatoriana. */
    function cedulaValida(c) {
        if (!/^\d{10}$/.test(c)) return false;
        const prov = parseInt(c.substring(0, 2), 10);
        if ((prov < 1 || prov > 24) && prov !== 30) return false;
        if (parseInt(c[2], 10) > 5) return false;
        let suma = 0;
        for (let i = 0; i < 9; i++) {
            let v = parseInt(c[i], 10) * (i % 2 === 0 ? 2 : 1);
            suma += v > 9 ? v - 9 : v;
        }
        return (10 - (suma % 10)) % 10 === parseInt(c[9], 10);
    }

    function edad(fechaISO) {
        const n = new Date(fechaISO + 'T00:00:00');
        const hoy = new Date();
        let e = hoy.getFullYear() - n.getFullYear();
        const m = hoy.getMonth() - n.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < n.getDate())) e--;
        return e;
    }

    const reglas = {
        cedula: v => cedulaValida(v) || 'Ingrese una cédula ecuatoriana válida de 10 dígitos.',
        nombres: v => /^[\p{L}][\p{L} '\-]{1,79}$/u.test(v) || 'Los nombres deben tener entre 2 y 80 letras.',
        apellidos: v => /^[\p{L}][\p{L} '\-]{1,79}$/u.test(v) || 'Los apellidos deben tener entre 2 y 80 letras.',
        correo: v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) || 'Ingrese un correo electrónico válido.',
        telefono: v => /^09\d{8}$/.test(v) || 'El celular debe tener el formato 09XXXXXXXX.',
        fecha_nacimiento: v => {
            if (!v) return 'Ingrese una fecha válida.';
            const e = edad(v);
            return (e >= 16 && e <= 90) || 'La edad debe estar entre 16 y 90 años.';
        },
        carrera: v => v !== '' || 'Seleccione una carrera de la lista.',
        semestre: v => (/^\d+$/.test(v) && +v >= 1 && +v <= 10) || 'El semestre debe estar entre 1 y 10.'
    };

    function validarCampo(campo) {
        const regla = reglas[campo.name];
        if (!regla) return true;
        const resultado = regla(campo.value.trim());
        const msg = document.getElementById('err-' + campo.name);
        if (resultado === true) {
            campo.removeAttribute('aria-invalid');
            campo.classList.add('valido');
            if (msg) msg.textContent = '';
            return true;
        }
        campo.setAttribute('aria-invalid', 'true');
        campo.classList.remove('valido');
        if (msg) msg.textContent = resultado;
        return false;
    }

    // Solo dígitos en cédula y teléfono
    ['cedula', 'telefono'].forEach(id => {
        const el = document.getElementById(id);
        el.addEventListener('input', () => { el.value = el.value.replace(/\D/g, '').slice(0, 10); });
    });

    form.querySelectorAll('input, select').forEach(campo => {
        campo.addEventListener('blur', () => validarCampo(campo));
        campo.addEventListener('change', () => validarCampo(campo));
    });

    form.addEventListener('submit', evt => {
        let ok = true;
        let primero = null;
        form.querySelectorAll('input:not([type=hidden]), select').forEach(campo => {
            if (!validarCampo(campo)) {
                ok = false;
                primero = primero || campo;
            }
        });
        if (!ok) {
            evt.preventDefault();
            primero.focus();
        }
    });

    form.addEventListener('reset', () => {
        form.querySelectorAll('[aria-invalid], .valido').forEach(c => {
            c.removeAttribute('aria-invalid');
            c.classList.remove('valido');
        });
        form.querySelectorAll('.error').forEach(m => { m.textContent = ''; });
    });
})();
