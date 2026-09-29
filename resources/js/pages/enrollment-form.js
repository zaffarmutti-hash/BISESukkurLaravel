const form = document.getElementById('enrollmentForm');

if (form) {
    const sections = [...form.querySelectorAll('.form-section')];
    const steps = [...document.querySelectorAll('.stepper-item[data-step]')];
    const progressFill = document.getElementById('efProgressFill');
    const progressLabel = document.getElementById('efProgressLabel');
    const progressCount = document.getElementById('efProgressCount');
    const announcement = document.getElementById('efAnnouncement');
    const transferBoard = document.getElementById('other_board');
    const transferFields = document.getElementById('transferFields');
    let currentStep = 1;

    const announce = (message, state = 'info') => {
        if (!announcement) return;
        announcement.dataset.state = state;
        announcement.textContent = message;
    };

    window.enrollmentAnnounce = announce;
    window.alert = (message) => announce(message, 'error');

    function fieldGroup(field) {
        return field.closest('.form-group') || field.closest('.passport-photo-card');
    }

    function setFieldError(field, message) {
        const group = fieldGroup(field);
        if (!group) return;

        let error = group.querySelector('.field-error');
        if (!error) {
            error = document.createElement('span');
            error.className = 'field-error';
            error.id = `${field.id || field.name}-error`;
            group.appendChild(error);
        }

        error.textContent = message;
        field.setAttribute('aria-invalid', 'true');
        const describedBy = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
        describedBy.add(error.id);
        field.setAttribute('aria-describedby', [...describedBy].join(' '));
    }

    function clearFieldError(field) {
        const group = fieldGroup(field);
        if (!group) return;
        group.querySelector('.field-error')?.remove();
        field.removeAttribute('aria-invalid');
        field.classList.remove('is-invalid');
    }

    function isVisibleField(field) {
        return !field.disabled && !field.closest('[hidden]');
    }

    function updateTransferFields() {
        if (!transferBoard || !transferFields) return;
        const hasTransfer = Boolean(transferBoard.value);
        transferFields.hidden = !hasTransfer;

        const boardName = document.getElementById('other_board_name');
        if (boardName) {
            boardName.required = transferBoard.value === 'other';
            if (!boardName.required) clearFieldError(boardName);
        }
    }

    function sectionIsComplete(section) {
        const requiredFields = [...section.querySelectorAll('[required]')].filter(isVisibleField);
        if (!requiredFields.length) return section.id === 'sec-other-board' ? true : false;

        const handledRadios = new Set();
        return requiredFields.every((field) => {
            if (field.type === 'radio') {
                if (handledRadios.has(field.name)) return true;
                handledRadios.add(field.name);
                return Boolean(section.querySelector(`input[type="radio"][name="${CSS.escape(field.name)}"]:checked`));
            }
            return field.type === 'checkbox' ? field.checked : Boolean(field.value.trim());
        });
    }

    function updateProgress(step) {
        currentStep = step;
        const section = sections.find((item) => Number(item.dataset.step) === step);
        const completeCount = sections.filter(sectionIsComplete).length;

        steps.forEach((link) => {
            const linkStep = Number(link.dataset.step);
            link.classList.toggle('active', linkStep === step);
            link.classList.toggle('is-complete', linkStep < step && sectionIsComplete(sections[linkStep - 1]));
            if (linkStep === step) link.setAttribute('aria-current', 'step');
            else link.removeAttribute('aria-current');
        });

        if (progressFill) progressFill.style.width = `${(step / sections.length) * 100}%`;
        if (progressLabel) progressLabel.textContent = `Step ${step} of ${sections.length}${section ? ` · ${section.dataset.title}` : ''}`;
        if (progressCount) progressCount.textContent = `${completeCount} of ${sections.length} sections complete`;
    }

    function refreshCompletion() {
        steps.forEach((link) => {
            const step = Number(link.dataset.step);
            link.classList.toggle('is-complete', step !== currentStep && sectionIsComplete(sections[step - 1]));
        });
        const completeCount = sections.filter(sectionIsComplete).length;
        if (progressCount) progressCount.textContent = `${completeCount} of ${sections.length} sections complete`;
    }

    function applyServerErrors() {
        document.querySelectorAll('#errorSummary [data-error-target]').forEach((item) => {
            const field = document.getElementById(item.dataset.errorTarget);
            if (field) setFieldError(field, item.textContent.trim());
        });
    }

    function installSubjectGroups() {
        const classSelect = document.getElementById('class_level');
        const groupSelect = document.getElementById('subject_group');
        if (!classSelect || !groupSelect) return;

        const oldValue = groupSelect.dataset.oldValue || '';
        const groupsByLevel = {
            ssc_part1: [
                ['science', 'Science (Biology)'],
                ['science', 'Science (Computer Science)'],
                ['general', 'General / Arts Group'],
            ],
            ssc_part2: [
                ['science', 'Science (Biology)'],
                ['science', 'Science (Computer Science)'],
                ['general', 'General / Arts Group'],
            ],
            hsc_part1: [
                ['pre_medical', 'Pre-Medical'],
                ['pre_engineering', 'Pre-Engineering'],
                ['science', 'General Science / Computer Science'],
                ['commerce', 'Commerce'],
                ['arts', 'Humanities / Arts'],
            ],
            hsc_part2: [
                ['pre_medical', 'Pre-Medical'],
                ['pre_engineering', 'Pre-Engineering'],
                ['science', 'General Science / Computer Science'],
                ['commerce', 'Commerce'],
                ['arts', 'Humanities / Arts'],
            ],
        };

        const renderGroups = () => {
            const selected = groupSelect.value || oldValue;
            groupSelect.replaceChildren(new Option('Select Group...', ''));
            (groupsByLevel[classSelect.value] || []).forEach(([value, label]) => {
                const option = new Option(label, value);
                option.selected = value === selected;
                groupSelect.add(option);
            });
            groupSelect.disabled = !classSelect.value;
            if (classSelect.value) groupSelect.removeAttribute('disabled');
        };

        classSelect.addEventListener('change', () => {
            groupSelect.value = '';
            renderGroups();
            clearFieldError(groupSelect);
            refreshCompletion();
        });
        renderGroups();
    }

    function installSectionNavigation() {
        const observer = new IntersectionObserver((entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
            if (visible[0]) updateProgress(Number(visible[0].target.dataset.step));
        }, { rootMargin: '-150px 0px -58% 0px', threshold: 0 });

        sections.forEach((section) => observer.observe(section));
        steps.forEach((link) => {
            link.addEventListener('click', () => updateProgress(Number(link.dataset.step)));
        });
        updateProgress(1);
    }

    function installPhotoPreview() {
        const input = document.getElementById('photo');
        const picker = document.getElementById('photoPicker');
        const frame = document.getElementById('passportPhotoFrame');
        const preview = document.getElementById('photoPreviewImg');
        const placeholder = document.getElementById('photoPlaceholder');
        const changeButton = document.getElementById('photoChangeBtn');
        const removeButton = document.getElementById('photoRemoveBtn');
        if (!input || !picker || !frame || !preview || !placeholder) return;

        const chooseFile = () => input.click();
        picker.addEventListener('click', chooseFile);
        changeButton?.addEventListener('click', chooseFile);

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                input.value = '';
                setFieldError(input, 'Choose a JPG, PNG, or WebP image.');
                announce('The selected photo format is not supported.', 'error');
                return;
            }
            if (file.size > 500 * 1024) {
                input.value = '';
                setFieldError(input, 'The photo must be 500 KB or smaller.');
                announce('The photo is larger than 500 KB. Choose a smaller image.', 'error');
                return;
            }

            clearFieldError(input);
            const reader = new FileReader();
            reader.onload = (event) => {
                preview.src = event.target.result;
                preview.hidden = false;
                placeholder.hidden = true;
                removeButton.hidden = false;
                changeButton.hidden = false;
                announce(`${file.name} selected.`);
            };
            reader.readAsDataURL(file);
        });

        removeButton?.addEventListener('click', () => {
            input.value = '';
            preview.removeAttribute('src');
            preview.hidden = true;
            placeholder.hidden = false;
            removeButton.hidden = true;
            changeButton.hidden = true;
            clearFieldError(input);
            announce('Candidate photo removed.');
        });
    }

    form.addEventListener('invalid', (event) => {
        const field = event.target;
        if (field instanceof HTMLElement && field.validationMessage) {
            setFieldError(field, field.validationMessage);
        }
    }, true);

    form.addEventListener('input', (event) => {
        if (event.target instanceof HTMLElement) clearFieldError(event.target);
        refreshCompletion();
    });

    form.addEventListener('change', refreshCompletion);
    transferBoard?.addEventListener('change', updateTransferFields);

    applyServerErrors();
    installSubjectGroups();
    installSectionNavigation();
    installPhotoPreview();
    updateTransferFields();
}