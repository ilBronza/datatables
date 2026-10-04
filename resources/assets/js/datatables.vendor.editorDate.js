(function(window, document)
{
    window.ibDtSyncDateDisplay = function(input)
    {
        if (! input || typeof input.matches !== 'function' || ! input.matches('input.ib-dt-date-input'))
            return;

        const display = input.nextElementSibling;

        if (! display || ! display.classList.contains('ib-dt-date-display'))
            return;

        const formats = [input.getAttribute('data-date-input-format')];

        // Native controls may omit seconds when they are zero.
        if (input.type === 'datetime-local')
            formats.push('YYYY-MM-DD[T]HH:mm', 'YYYY-MM-DD[T]HH:mm:ss', 'YYYY-MM-DD[T]HH:mm:ss.SSS');
        else if (input.type === 'time')
            formats.push('HH:mm', 'HH:mm:ss', 'HH:mm:ss.SSS');

        const date = input.value ? window.moment(input.value, formats, true) : null;
        display.textContent = date && date.isValid()
            ? date.format(input.getAttribute('data-date-display-format')) : '';
    };

    window.ibDtRenderDateEditor = function(html, displayFormat, inputFormat)
    {
        const wrapper = document.createElement('span');
        wrapper.className = 'ib-dt-date-editor';
        wrapper.innerHTML = html;

        const input = wrapper.querySelector('input');
        input.classList.add('ib-dt-date-input');
        input.setAttribute('data-date-display-format', displayFormat);
        input.setAttribute('data-date-input-format', inputFormat);

        const display = document.createElement('span');
        display.className = 'ib-dt-date-display';
        display.setAttribute('aria-hidden', 'true');
        wrapper.appendChild(display);

        window.ibDtSyncDateDisplay(input);

        return wrapper.outerHTML;
    };

    // Delegation also covers editors inserted by row refreshes and redraws.
    ['input', 'change', 'focusout'].forEach(function(eventName)
    {
        document.addEventListener(eventName, function(event)
        {
            window.ibDtSyncDateDisplay(event.target);
        });
    });
})(window, document);
