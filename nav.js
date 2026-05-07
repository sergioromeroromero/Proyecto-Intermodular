document.addEventListener('DOMContentLoaded', function () {
    var links = [];
    var current = -1;

    function refreshLinks() {
        links = Array.from(document.querySelectorAll('a, button'));
    }

    function highlight(index) {
        if (current >= 0 && links[current]) {
            links[current].style.outline = '';
        }
        current = index;
        if (links[current]) {
            links[current].style.outline = '2px solid #2980b9';
            links[current].scrollIntoView({ block: 'nearest' });
        }
    }

    refreshLinks();

    document.addEventListener('keydown', function (e) {
        if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            refreshLinks();
            highlight(current < links.length - 1 ? current + 1 : 0);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            refreshLinks();
            highlight(current > 0 ? current - 1 : links.length - 1);
        } else if (e.key === 'Enter' && current >= 0 && links[current]) {
            links[current].click();
        }
    });
});
