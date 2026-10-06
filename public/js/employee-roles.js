(function () {
    'use strict'

    var panel = document.getElementById('employee-role-panel')
    var form = document.getElementById('employee-role-form')
    var modal = document.getElementById('employee-role-modal')
    if (!panel) return

    var rows = document.getElementById('employee-role-rows')
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

    function closeModal() {
        if (!modal) return
        modal.classList.remove('modal-open')
        modal.setAttribute('aria-hidden', 'true')
        document.body.classList.remove('modal-body-open')
    }

    function updateCount(element, amount) {
        element.textContent = String(Math.max(0, Number(element.textContent || 0) + amount))
    }

    function openModal(role) {
        if (!form || !modal) return
        editingId = role ? role.id : null
        form.elements.name.value = role ? role.name : ''
        form.elements.description.value = role ? (role.description || '') : ''
        form.querySelector('.form-error').hidden = true
        form.querySelector('.form-error').textContent = ''
        document.getElementById('employee-role-modal-title').textContent = role ? 'Edit employee role' : 'Add employee role'
        form.querySelector('[type="submit"]').textContent = role ? 'Save changes' : 'Add role'
        modal.classList.add('modal-open')
        modal.setAttribute('aria-hidden', 'false')
        document.body.classList.add('modal-body-open')
        form.elements.name.focus()
    }

    function makeActionButton(action, role) {
        var button = document.createElement('button')
        var isActive = Boolean(role.is_active)
        button.className = 'icon-button' + (action === 'status' && isActive ? ' icon-button-danger' : '')
        button.type = 'button'
        if (action === 'edit') {
            button.setAttribute('aria-label', 'Edit ' + role.name)
            button.title = 'Edit role'
            button.setAttribute('data-role-edit', role.id)
            button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>'
        } else {
            button.setAttribute('data-role-status', role.id)
            button.setAttribute('aria-label', (isActive ? 'Deactivate ' : 'Activate ') + role.name)
            button.title = isActive ? 'Deactivate role' : 'Activate role'
            button.innerHTML = isActive
                ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>'
                : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>'
        }
        return button
    }

    function renderRole(role) {
        var row = document.createElement('tr')
        row.setAttribute('data-role-row', role.id)
        row.setAttribute('data-active', role.is_active ? '1' : '0')

        var nameCell = document.createElement('td')
        var name = document.createElement('strong')
        name.setAttribute('data-role-name', '')
        name.textContent = role.name
        nameCell.appendChild(name)

        var descriptionCell = document.createElement('td')
        descriptionCell.setAttribute('data-role-description', '')
        descriptionCell.textContent = role.description || '—'

        var employeeCell = document.createElement('td')
        employeeCell.setAttribute('data-role-employees', '')
        employeeCell.textContent = String(role.employees_count || 0)

        var statusCell = document.createElement('td')
        statusCell.setAttribute('data-role-status', '')
        statusCell.innerHTML = '<span class="status status-' + (role.is_active ? 'active' : 'negative') + '"><i></i>' + (role.is_active ? 'Active' : 'Inactive') + '</span>'

        var actionsCell = document.createElement('td')
        actionsCell.className = 'row-actions'
        if (canEdit) actionsCell.appendChild(makeActionButton('edit', role))
        if (canToggle) actionsCell.appendChild(makeActionButton('status', role))

        row.appendChild(nameCell)
        row.appendChild(descriptionCell)
        row.appendChild(employeeCell)
        row.appendChild(statusCell)
        row.appendChild(actionsCell)
        return row
    }

    function replaceRole(role, isNew) {
        var current = rows.querySelector('[data-role-row="' + role.id + '"]')
        if (isNew) updateCount(activeCount, 1)
        if (Boolean(role.is_active) !== (selectedStatus === 'active')) {
            if (current) {
                current.remove()
                visibleCount.textContent = String(Math.max(0, Number(visibleCount.textContent || 0) - 1))
                showEmptyRow()
            }
            return
        }
        var replacement = renderRole(role)
        if (current) {
            rows.replaceChild(replacement, current)
        } else {
            var emptyRow = rows.querySelector('[data-role-empty]')
            if (emptyRow) emptyRow.remove()
            rows.appendChild(replacement)
            visibleCount.textContent = String(Number(visibleCount.textContent || 0) + 1)
        }
    }

    function showEmptyRow() {
        if (rows.querySelector('[data-role-row]') || rows.querySelector('[data-role-empty]')) return
        var row = document.createElement('tr')
        row.setAttribute('data-role-empty', '')
        var cell = document.createElement('td')
        cell.colSpan = 5
        cell.className = 'table-empty'
        cell.textContent = 'No ' + selectedStatus + ' employee roles found.'
        row.appendChild(cell)
        rows.appendChild(row)
    }

    function getRowRole(row) {
        return {
            id: row.getAttribute('data-role-row'),
            name: row.querySelector('[data-role-name]').textContent,
            description: row.querySelector('[data-role-description]').textContent === '—' ? '' : row.querySelector('[data-role-description]').textContent,
            employees_count: Number(row.querySelector('[data-role-employees]').textContent),
            is_active: row.getAttribute('data-active') === '1'
        }
    }

    panel.addEventListener('click', function (event) {
        var editButton = event.target.closest('[data-role-edit]')
        var statusButton = event.target.closest('[data-role-status]')
        if (editButton) {
            openModal(getRowRole(editButton.closest('[data-role-row]')))
        }
        if (statusButton) {
            var row = statusButton.closest('[data-role-row]')
            var role = getRowRole(row)
            var nextActive = !role.is_active
            var confirmation = window.Swal
                ? window.Swal.fire({
                    icon: 'warning',
                    title: (nextActive ? 'Activate' : 'Deactivate') + ' employee role?',
                    text: '"' + role.name + '" will be ' + (nextActive ? 'available for new employees.' : 'kept in the system but unavailable for new employees.'),
                    showCancelButton: true,
                    confirmButtonText: nextActive ? 'Activate role' : 'Deactivate role',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: nextActive ? '#16734e' : '#aa3d44'
                }).then(function (result) { return result.isConfirmed })
                : Promise.resolve(window.confirm('Are you sure you want to ' + (nextActive ? 'activate' : 'deactivate') + ' "' + role.name + '"?'))

            confirmation.then(function (confirmed) {
                if (!confirmed) return
                var url = panel.dataset.statusUrl.replace('__role__', role.id)
                request(url, 'PATCH', { is_active: nextActive ? 1 : 0 }).then(function (payload) {
                    var updated = payload.data
                    if (role.is_active !== Boolean(updated.is_active)) {
                        updateCount(role.is_active ? activeCount : inactiveCount, -1)
                        updateCount(updated.is_active ? activeCount : inactiveCount, 1)
                    }
                    replaceRole(updated, false)
                    toastSuccess(payload.message || 'Employee role status updated.')
                }).catch(function (error) { alertError(error.message) })
            })
        }
    })

    document.querySelectorAll('[data-role-create]').forEach(function (button) {
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
            var url = editingId ? panel.dataset.updateUrl.replace('__role__', editingId) : panel.dataset.storeUrl
            var method = editingId ? 'PUT' : 'POST'
            request(url, method, body).then(function (payload) {
                replaceRole(payload.data, !editingId)
                closeModal()
                toastSuccess(payload.message || 'Employee role saved.')
            }).catch(function (error) {
                errorBox.textContent = error.message
                errorBox.hidden = false
            })
        })
    }
})()
