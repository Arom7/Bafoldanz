/**
 * Formulario de alta/edición de bailarines.
 * Sincroniza la sucursal del grupo elegido y, sin promoción, toma la tarifa
 * registrada en esa sucursal. Los selects usan un desplegable interno para no
 * desbordar el viewport móvil.
 */

let selectSeq = 0;

function enhanceSelect(select) {
    const parent = select.parentElement;
    if (!parent || parent.querySelector('.select-menu')) return;

    const id = select.id || `student-select-${++selectSeq}`;
    const menu = document.createElement('div');
    menu.className = 'select-menu';
    menu.id = `${id}-menu`;
    menu.setAttribute('role', 'listbox');
    menu.setAttribute('aria-hidden', 'true');

    const overlay = document.createElement('button');
    overlay.type = 'button';
    overlay.className = 'select-overlay';
    overlay.tabIndex = -1;
    overlay.setAttribute('aria-hidden', 'true');

    select.setAttribute('aria-haspopup', 'listbox');
    select.setAttribute('aria-controls', menu.id);
    select.setAttribute('aria-expanded', 'false');
    parent.append(overlay, menu);

    let options = [];
    let activeIndex = -1;

    const setActive = (index) => {
        if (!options.length) return;

        activeIndex = (index + options.length) % options.length;
        const rows = menu.querySelectorAll('.select-option');
        rows.forEach((row, i) => row.classList.toggle('is-active', i === activeIndex));
        select.setAttribute('aria-activedescendant', rows[activeIndex]?.id ?? '');
    };

    const close = () => {
        menu.setAttribute('aria-hidden', 'true');
        select.setAttribute('aria-expanded', 'false');
        select.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    };

    const choose = (index) => {
        const option = options[index];
        if (!option) return;

        select.value = option.value;
        close();
        select.dispatchEvent(new Event('change', { bubbles: true }));
        select.focus();
    };

    const buildMenu = () => {
        options = Array.from(select.options);
        menu.replaceChildren();

        options.forEach((option, index) => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'select-option';
            row.id = `${menu.id}-option-${index}`;
            row.setAttribute('role', 'option');
            row.setAttribute('aria-selected', option.selected ? 'true' : 'false');
            row.textContent = option.textContent.trim();
            row.addEventListener('click', () => choose(index));
            row.addEventListener('pointerenter', () => setActive(index));
            menu.append(row);
        });
    };

    const open = () => {
        buildMenu();
        menu.setAttribute('aria-hidden', 'false');
        select.setAttribute('aria-expanded', 'true');

        const selected = options.findIndex((option) => option.selected);
        setActive(selected >= 0 ? selected : 0);

        const rect = select.getBoundingClientRect();
        menu.dataset.placement = window.innerHeight - rect.bottom < 240 ? 'top' : 'bottom';
    };

    const isOpen = () => menu.getAttribute('aria-hidden') === 'false';

    overlay.addEventListener('click', (event) => {
        event.preventDefault();
        if (isOpen()) {
            close();
            return;
        }
        select.focus();
        open();
    });

    select.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            event.preventDefault();
            close();
            return;
        }
        if (event.key === 'Tab' && isOpen()) {
            close();
            return;
        }
        if (!['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) return;

        event.preventDefault();
        if (!isOpen()) {
            open();
        } else if (event.key === 'ArrowDown') {
            setActive(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            setActive(activeIndex - 1);
        } else {
            choose(activeIndex);
        }
    });

    document.addEventListener('pointerdown', (event) => {
        if (isOpen() && !parent.contains(event.target)) close();
    });
}

export function initStudentForm() {
    const form = document.querySelector('[data-student-form]');
    const group = document.getElementById('group_id');
    const branch = document.getElementById('branch_id');
    const fee = document.getElementById('fee');
    const promo = document.getElementById('is_promo');

    if (form) {
        form.querySelectorAll('select').forEach(enhanceSelect);
    }

    const syncBranch = () => {
        if (!branch || !group) return;
        branch.value = group.selectedOptions[0]?.dataset.branchId ?? '';
    };

    const syncFee = () => {
        if (!fee || !group) return;

        const isPromo = promo?.checked ?? false;
        fee.readOnly = !isPromo;
        fee.setAttribute('aria-readonly', isPromo ? 'false' : 'true');

        if (isPromo) return;

        const branchFee = group.selectedOptions[0]?.dataset.branchFee ?? '';
        if (branchFee !== '') {
            fee.value = branchFee;
        }
    };

    const syncPromo = () => {
        promo?.setAttribute('aria-checked', promo.checked ? 'true' : 'false');
        syncFee();
    };

    group?.addEventListener('change', () => {
        syncBranch();
        syncFee();
    });

    promo?.addEventListener('change', syncPromo);

    syncBranch();
    syncPromo();
}
