(function () {
    'use strict'

    var panel = document.getElementById('employee-type-panel')
    var form = document.getElementById('employee-type-form')
    var modal = document.getElementById('employee-type-modal')
    if (!panel) return

    var rows = document.getElementById('employee-type-rows')
    var visibleCount = document.querySelector('[data-visible-count]')
    var activeCount = document.querySelector('[data-active-count]')
    var inactiveCount = document.querySelector('[data-inactive-count]')
    var csrf = document.querySelector('meta[name="csrf-token"]')
    var selectedStatus = panel.dataset.status
    var editingId = null
    var canEdit = panel.dataset.canEdit === '1'
    var canToggle = panel.dataset.canToggle === '1'

    function alertError(message) {
        if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Unable to complete', text: message })
        } else {
            window.alert(message)
        }
    }

    function toastSuccess(message) {
        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true
            })
        } else {
            window.alert(message)
        }
    }

    function request(url, method, body) {
        var headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
        if (csrf) headers['X-CSRF-TOKEN'] = csrf.content
        if (body) headers['Content-Type'] = 'application/json'

        return fetch(url, {
            method: method,
            credentials: 'same-origin',
            headers: headers,
            body: body ? JSON.stringify(body) : undefined
        }).then(function (response) {
            return response.text().then(function (text) {
                var payload
                try {
                    payload = text ? JSON.parse(text) : {}
                } catch (error) {
                    throw new Error('The server returned an unexpected response.')
                }
                if (!response.ok) {
                    var errors = payload.errors ? Object.keys(payload.errors) : []
                    throw new Error(errors.length ? payload.errors[errors[0]][0] : (payload.message || 'The request failed.'))
                }
                return payload
            })
        })
    }

    function updateCount(element, amount) {
        element.textContent = String(Math.max(0, Number(element.textContent || 0) + amount))
    }

    function closeModal() {
        if (!modal) return
        modal.classList.remove('modal-open')
        modal.setAttribute('aria-hidden', 'true')
        document.body.classList.remove('modal-body-open')
    }

    function openModal(type) {
        if (!form || !modal) return
        editingId = type ? type.id : null
        form.elements.name.value = type ? type.name : ''
        form.elements.description.value = type ? (type.description || '') : ''
        form.querySelector('.form-error').hidden = true
        form.querySelector('.form-error').textContent = ''
        document.getElementById('employee-type-modal-title').textContent = type ? 'Edit employee type' : 'Add employee type'
        form.querySelector('[type="submit"]').textContent = type ? 'Save changes' : 'Add type'
        modal.classList.add('modal-open')
        modal.setAttribute('aria-hidden', 'false')
        document.body.classList.add('modal-body-open')
        form.elements.name.focus()
    }

    function makeButton(action, type) {
        var button = document.createElement('button')
        var isActive = Boolean(type.is_active)
        button.className = 'icon-button' + (action === 'status' && isActive ? ' icon-button-danger' : '')
        button.type = 'button'
        if (action === 'edit') {
            button.setAttribute('data-type-edit', type.id)
            button.setAttribute('aria-label', 'Edit ' + type.name)
            button.title = 'Edit type'
            button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>'
        } else {
            button.setAttribute('data-type-status', type.id)
            button.setAttribute('aria-label', (isActive ? 'Deactivate ' : 'Activate ') + type.name)
            button.title = isActive ? 'Deactivate type' : 'Activate type'
            button.innerHTML = isActive
                ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>'
                : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>'
        }
        return button
    }

    function renderType(type) {
        var row = document.createElement('tr')
        var isActive = Boolean(type.is_active)
        row.setAttribute('data-type-row', type.id)
        row.setAttribute('data-active', isActive ? '1' : '0')

        var nameCell = document.createElement('td')
        var name = document.createElement('strong')
        name.setAttribute('data-type-name', '')
        name.textContent = type.name
        nameCell.appendChild(name)

        var descriptionCell = document.createElement('td')
        descriptionCell.setAttribute('data-type-description', '')
        descriptionCell.textContent = type.description || '—'

        var employeeCell = document.createElement('td')
        employeeCell.setAttribute('data-type-employees', '')
        employeeCell.textContent = String(type.employees_count || 0)

        var statusCell = document.createElement('td')
        statusCell.setAttribute('data-type-status', '')
        statusCell.innerHTML = '<span class="status status-' + (isActive ? 'active' : 'negative') + '"><i></i>' + (isActive ? 'Active' : 'Inactive') + '</span>'

        var actionsCell = document.createElement('td')
        actionsCell.className = 'row-actions'
        if (canEdit) actionsCell.appendChild(makeButton('edit', type))
        if (canToggle) actionsCell.appendChild(makeButton('status', type))

        row.appendChild(nameCell)
        row.appendChild(descriptionCell)
        row.appendChild(employeeCell)
        row.appendChild(statusCell)
        row.appendChild(actionsCell)
        return row
    }

    function getRowType(row) {
        return {
            id: row.getAttribute('data-type-row'),
            name: row.querySelector('[data-type-name]').textContent,
            description: row.querySelector('[data-type-description]').textContent === '—' ? '' : row.querySelector('[data-type-description]').textContent,
            employees_count: Number(row.querySelector('[data-type-employees]').textContent),
            is_active: row.getAttribute('data-active') === '1'
        }
    }

    function emptyRow() {
        var row = document.createElement('tr')
        row.setAttribute('data-type-empty', '')
        var cell = document.createElement('td')
        cell.colSpan = 5
        cell.className = 'table-empty'
        cell.textContent = 'No ' + selectedStatus + ' employee types found.'
        row.appendChild(cell)
        return row
    }

    function ensureEmptyRow() {
        if (!rows.querySelector('[data-type-row]') && !rows.querySelector('[data-type-empty]')) {
            rows.appendChild(emptyRow())
        }
    }

    function replaceType(type, isNew) {
        var current = rows.querySelector('[data-type-row="' + type.id + '"]')
        if (isNew) {
            updateCount(activeCount, 1)
        }
        if (Boolean(type.is_active) !== (selectedStatus === 'active')) {
            if (current) {
                current.remove()
                visibleCount.textContent = String(Math.max(0, Number(visibleCount.textContent || 0) - 1))
                ensureEmptyRow()
            }
            return
        }

        var replacement = renderType(type)
        if (current) {
            rows.replaceChild(replacement, current)
        } else {
            var empty = rows.querySelector('[data-type-empty]')
            if (empty) empty.remove()
            rows.appendChild(replacement)
            visibleCount.textContent = String(Number(visibleCount.textContent || 0) + 1)
        }
    }

    panel.addEventListener('click', function (event) {
        var editButton = event.target.closest('[data-type-edit]')
        var statusButton = event.target.closest('[data-type-status]')
        if (editButton) {
            openModal(getRowType(editButton.closest('[data-type-row]')))
        }
        if (statusButton) {
            var row = statusButton.closest('[data-type-row]')
            var type = getRowType(row)
            var nextActive = !type.is_active
            var actionName = nextActive ? 'activate' : 'deactivate'
            var confirmation = window.Swal
                ? window.Swal.fire({
                    icon: 'warning',
                    title: (nextActive ? 'Activate' : 'Deactivate') + ' employee type?',
                    text: '"' + type.name + '" will be ' + (nextActive ? 'available for new employees.' : 'kept in the system but unavailable for new employees.'),
                    showCancelButton: true,
                    confirmButtonText: nextActive ? 'Activate type' : 'Deactivate type',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: nextActive ? '#16734e' : '#aa3d44'
                }).then(function (result) { return result.isConfirmed })
                : Promise.resolve(window.confirm('Are you sure you want to ' + actionName + ' "' + type.name + '"?'))

            confirmation.then(function (confirmed) {
                if (!confirmed) return
                var url = panel.dataset.statusUrl.replace('__type__', type.id)
                request(url, 'PATCH', { is_active: nextActive ? 1 : 0 }).then(function (payload) {
                    var updated = payload.data
                    if (type.is_active !== Boolean(updated.is_active)) {
                        updateCount(type.is_active ? activeCount : inactiveCount, -1)
                        updateCount(updated.is_active ? activeCount : inactiveCount, 1)
                    }
                    replaceType(updated, false)
                    toastSuccess(payload.message || 'Employee type status updated.')
                }).catch(function (error) { alertError(error.message) })
            })
        }
    })

    document.querySelectorAll('[data-type-create]').forEach(function (button) {
        button.addEventListener('click', function () { openModal(null) })
    })

    if (modal) {
        modal.querySelectorAll('.modal-close, .modal-cancel').forEach(function (button) {
            button.addEventListener('click', closeModal)
        })
        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal()
        })
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault()
            var errorBox = form.querySelector('.form-error')
            errorBox.hidden = true
            var body = {
                name: form.elements.name.value.trim(),
                description: form.elements.description.value.trim()
            }
            var url = editingId ? panel.dataset.updateUrl.replace('__type__', editingId) : panel.dataset.storeUrl
            var method = editingId ? 'PUT' : 'POST'
            request(url, method, body).then(function (payload) {
                replaceType(payload.data, !editingId)
                closeModal()
                toastSuccess(payload.message || 'Employee type saved.')
            }).catch(function (error) {
                errorBox.textContent = error.message
                errorBox.hidden = false
            })
        })
    }
})()
