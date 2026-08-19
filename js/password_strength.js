window.CityVetPassword = {
    message(password) {
        if (password.length < 8) return 'Use at least 8 characters.';
        if (!/[A-Z]/.test(password)) return 'Add an uppercase letter.';
        if (!/[a-z]/.test(password)) return 'Add a lowercase letter.';
        if (!/\d/.test(password)) return 'Add a number.';
        if (!/[^A-Za-z0-9]/.test(password)) return 'Add a special character.';
        return '';
    },
    valid(password) { return this.message(password) === ''; },
    bind(inputId, hintId) {
        const input = document.getElementById(inputId);
        const hint = document.getElementById(hintId);
        if (!input || !hint) return;
        const update = () => {
            const message = this.message(input.value);
            hint.textContent = input.value ? (message || 'Strong password') : 'At least 8 characters, with uppercase, lowercase, number, and special character.';
            hint.className = `mt-1 text-xs ${message ? 'text-red-600' : 'text-green-600'}`;
        };
        input.addEventListener('input', update);
        update();
    }
};
