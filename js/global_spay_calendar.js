(function () {
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    let viewDate = new Date();
    let events = [];
    let config = {};

    function eventByDate(dateStr) {
        return events.find(event => event.Event_Date === dateStr);
    }

    function render() {
        const grid = document.getElementById(config.gridId);
        const monthLabel = document.getElementById(config.monthId);
        if (!grid || !monthLabel) return;

        const month = viewDate.getMonth();
        const year = viewDate.getFullYear();
        monthLabel.innerText = `${monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        let html = '';

        for (let i = 0; i < firstDay; i++) {
            html += '<div class="h-20 rounded-lg bg-gray-50 border border-gray-100 opacity-50"></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const event = eventByDate(dateStr);
            const isFull = event && (event.Is_Full || parseInt(event.Remaining_Slots, 10) <= 0 || event.Status === 'Closed');
            const cellClass = event
                ? (isFull ? 'bg-red-50 border-red-100 text-red-700' : 'bg-teal-50 border-teal-200 text-[#00796B]')
                : 'bg-white border-gray-200 text-gray-400';
            const badge = event
                ? `<span class="mt-1 inline-block rounded px-2 py-1 text-[10px] font-bold ${isFull ? 'bg-red-100 text-red-700' : 'bg-white text-[#00796B]'}">${isFull ? 'Fully Booked' : event.Remaining_Slots + ' slots'}</span>`
                : '';

            html += `
                <div class="h-20 rounded-lg border p-2 flex flex-col items-center justify-center ${cellClass}">
                    <span class="text-sm font-black">${day}</span>
                    ${event ? `<span class="mt-1 text-[10px] font-bold leading-tight">${event.Title}</span>` : ''}
                    ${badge}
                </div>
            `;
        }

        grid.innerHTML = html;
    }

    async function load() {
        try {
            const res = await fetch('../../api/calendar/events.php');
            const data = await res.json();
            events = data.status === 'success' ? data.events : [];
        } catch (error) {
            events = [];
        }
        render();
    }

    window.CityVetSpayCalendar = {
        init(options) {
            config = options;
            load();
        },
        changeMonth(dir) {
            viewDate.setMonth(viewDate.getMonth() + dir);
            render();
        }
    };
})();
