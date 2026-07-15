(function () {
    const barangays = [
        'Agusan', 'Baikingon', 'Balubal', 'Balulang', 'Bayabas', 'Bayanga', 'Besigan', 'Bonbon',
        'Bugo', 'Bulua', 'Camaman-an', 'Canito-an', 'Carmen', 'Consolacion', 'Cugman', 'Dansolihon',
        'F.S. Catanico', 'Gusa', 'Indahag', 'Iponan', 'Kauswagan', 'Lapasan', 'Lumbia', 'Macabalan',
        'Macasandig', 'Mambuaya', 'Nazareth', 'Pagalungan', 'Pagatpat', 'Patag', 'Pigsag-an', 'Puerto',
        'Puntod', 'San Simon', 'Tablon', 'Taglimao', 'Tagpangi', 'Tignapoloan', 'Tuburan', 'Tumpagon',
        'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6',
        'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10', 'Barangay 11', 'Barangay 12',
        'Barangay 13', 'Barangay 14', 'Barangay 15', 'Barangay 16', 'Barangay 17', 'Barangay 18',
        'Barangay 19', 'Barangay 20', 'Barangay 21', 'Barangay 22', 'Barangay 23', 'Barangay 24',
        'Barangay 25', 'Barangay 26', 'Barangay 27', 'Barangay 28', 'Barangay 29', 'Barangay 30',
        'Barangay 31', 'Barangay 32', 'Barangay 33', 'Barangay 34', 'Barangay 35', 'Barangay 36',
        'Barangay 37', 'Barangay 38', 'Barangay 39', 'Barangay 40'
    ];

    function populateBarangaySelect(select) {
        if (!select) return;
        const selectedValue = select.value;
        const optionClass = select.dataset.optionClass || '';
        select.innerHTML = `<option value="" disabled selected${optionClass ? ` class="${optionClass}"` : ''}>Select Barangay</option>` +
            barangays.map(name => `<option value="${name}"${optionClass ? ` class="${optionClass}"` : ''}>${name}</option>`).join('');
        if (selectedValue) select.value = selectedValue;
    }

    window.CityVetBarangays = {
        list: barangays,
        populateAll() {
            document.querySelectorAll('select[data-cdo-barangays], select#barangay').forEach(populateBarangaySelect);
        }
    };

    document.addEventListener('DOMContentLoaded', () => window.CityVetBarangays.populateAll());
})();
