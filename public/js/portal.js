(function () {
    'use strict'

    var csrfMeta = document.querySelector('meta[name="csrf-token"]')
    var toast = document.getElementById('portal-toast')
    var toastTimer

    function showToast(message, isError) {
        if (!toast) return
        toast.textContent = message
        toast.classList.toggle('toast-error', Boolean(isError))
        toast.classList.add('toast-visible')
        window.clearTimeout(toastTimer)
        toastTimer = window.setTimeout(function () {
            toast.classList.remove('toast-visible')
        }, 4200)
    }

    function request(url, options) {
        options = options || {}
        var headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
        if (options.body) headers['Content-Type'] = 'application/json'
        if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content

        return fetch(url, Object.assign({ credentials: 'same-origin' }, options, {
            headers: Object.assign(headers, options.headers || {})
        })).then(function (response) {
            return response.text().then(function (text) {
                var payload
                try {
                    payload = text ? JSON.parse(text) : {}
                } catch (error) {
                    throw new Error('The server returned an unexpected response.')
                }
                if (!response.ok) {
                    var keys = payload.errors ? Object.keys(payload.errors) : []
                    var validationError = keys.length ? payload.errors[keys[0]][0] : null
                    throw new Error(validationError || payload.message || 'The request could not be completed.')
                }
                return payload
            })
        })
    }

    function showFormError(form, message) {
        var error = form.querySelector('.form-error')
        if (!error) {
            showToast(message, true)
            return
        }
        error.textContent = message
        error.hidden = false
    }

    function clearFormError(form) {
        var error = form.querySelector('.form-error')
        if (error) {
            error.textContent = ''
            error.hidden = true
        }
    }

    function openModal(modal) {
        if (!modal) return
        modal.classList.add('modal-open')
        modal.setAttribute('aria-hidden', 'false')
        document.body.classList.add('modal-body-open')
        var focusable = modal.querySelector('input:not([type=hidden]), select, textarea, button')
        if (focusable) window.setTimeout(function () { focusable.focus() }, 60)
    }

    function closeModal(modal) {
        if (!modal) return
        modal.classList.remove('modal-open')
        modal.setAttribute('aria-hidden', 'true')
        if (!document.querySelector('.modal-backdrop.modal-open')) {
            document.body.classList.remove('modal-body-open')
        }
    }

    var sidebar = document.getElementById('portal-sidebar')
    document.querySelectorAll('[data-menu-toggle], [data-menu-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (sidebar) sidebar.classList.toggle('sidebar-open')
            document.body.classList.toggle('navigation-open')
        })
    })

    var directoryPanel = document.querySelector('[data-directory-panel]')

    function loadEmployeeDirectory(page) {
        if (!directoryPanel) return
        var form = directoryPanel.querySelector('[data-directory-filters]')
        var params = new URLSearchParams(new FormData(form))
        params.set('page', String(page || 1))
        params.set('sort', directoryPanel.dataset.sort || 'last_name')
        params.set('direction', directoryPanel.dataset.direction || 'asc')
        directoryPanel.classList.add('is-loading')

        request(directoryPanel.dataset.url + '?' + params.toString())
            .then(function (payload) {
                var meta = payload.meta || { current_page: 1, last_page: 1, total: 0 }
                document.getElementById('employee-table').innerHTML = payload.html || ''
                directoryPanel.dataset.page = meta.current_page
                document.querySelector('[data-directory-total]').textContent = Number(meta.total || 0).toLocaleString('en-IN')
                directoryPanel.querySelector('[data-directory-summary]').textContent =
                    'Showing ' + (meta.total ? ((meta.current_page - 1) * 50 + 1) : 0) +
                    '–' + Math.min(meta.current_page * 50, meta.total || 0) +
                    ' of ' + Number(meta.total || 0).toLocaleString('en-IN') + ' people'
                directoryPanel.querySelector('[data-directory-page]').textContent =
                    'Page ' + meta.current_page + ' of ' + meta.last_page
                directoryPanel.querySelector('[data-directory-prev]').disabled = meta.current_page <= 1
                directoryPanel.querySelector('[data-directory-next]').disabled = meta.current_page >= meta.last_page
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', window.location.pathname + '?' + params.toString())
                }
            })
            .catch(function (error) {
                showToast(error.message, true)
            })
            .then(function () {
                directoryPanel.classList.remove('is-loading')
            })
    }

    if (directoryPanel) {
        var directoryForm = directoryPanel.querySelector('[data-directory-filters]')
        var searchTimer
        directoryForm.addEventListener('submit', function (event) {
            event.preventDefault()
            loadEmployeeDirectory(1)
        })
        directoryForm.elements.search.addEventListener('input', function () {
            window.clearTimeout(searchTimer)
            searchTimer = window.setTimeout(function () { loadEmployeeDirectory(1) }, 300)
        })
        directoryForm.querySelectorAll('select, input[type="date"]').forEach(function (field) {
            field.addEventListener('change', function () { loadEmployeeDirectory(1) })
        })
        directoryPanel.querySelector('[data-directory-prev]').addEventListener('click', function () {
            loadEmployeeDirectory(Math.max(1, Number(directoryPanel.dataset.page || 1) - 1))
        })
        directoryPanel.querySelector('[data-directory-next]').addEventListener('click', function () {
            loadEmployeeDirectory(Number(directoryPanel.dataset.page || 1) + 1)
        })
        directoryPanel.addEventListener('click', function (event) {
            var sortButton = event.target.closest('[data-directory-sort]')
            if (!sortButton) return
            var sort = sortButton.dataset.directorySort
            directoryPanel.dataset.direction =
                directoryPanel.dataset.sort === sort && directoryPanel.dataset.direction === 'asc' ? 'desc' : 'asc'
            directoryPanel.dataset.sort = sort
            loadEmployeeDirectory(1)
        })
    }

    var employeeForm = document.getElementById('employee-form')
    if (employeeForm) {
        var departmentSelect = employeeForm.elements.department_id
        var roleSelect = employeeForm.elements.employee_role_id
        var typeSelect = employeeForm.elements.employee_type_id
        var lookupsPromise

        function loadLookup(url, select, placeholder) {
            return request(url).then(function (payload) {
                select.innerHTML = '<option value="">' + placeholder + '</option>'
                ;(payload.data || []).forEach(function (item) {
                    var option = document.createElement('option')
                    option.value = item.id
                    option.textContent = item.name
                    select.appendChild(option)
                })
            })
        }

        function loadEmployeeLookups() {
            if (!lookupsPromise) {
                lookupsPromise = Promise.all([
                    loadLookup(employeeForm.dataset.departmentsUrl, departmentSelect, 'Select department'),
                    loadLookup(employeeForm.dataset.typesUrl, typeSelect, 'Select employment type'),
                    loadLookup(employeeForm.dataset.rolesUrl, roleSelect, 'Select employee role')
                ]).catch(function (error) {
                    lookupsPromise = null
                    showToast(error.message, true)
                })
            }
            return lookupsPromise
        }

        loadEmployeeLookups()

        document.addEventListener('click', function (event) {
            var editButton = event.target.closest('.edit-employee')
            if (!editButton) return
            var employee
            try {
                employee = JSON.parse(editButton.dataset.employee)
            } catch (error) {
                showToast('The employee record could not be opened for editing.', true)
                return
            }
            employeeForm.reset()
            clearFormError(employeeForm)
            employeeForm.dataset.employeeId = employee.id
            employeeForm.querySelectorAll('[name]').forEach(function (field) {
                if (Object.prototype.hasOwnProperty.call(employee, field.name) && employee[field.name] !== null) {
                    field.value = employee[field.name]
                }
            })
            loadEmployeeLookups().then(function () {
                departmentSelect.value = employee.department_id || ''
                roleSelect.value = employee.employee_role_id || ''
                typeSelect.value = employee.employee_type_id || ''
            })
            document.getElementById('employee-modal-title').textContent = 'Edit employee'
            employeeForm.querySelector('[type=submit]').textContent = 'Save changes'
            openModal(document.getElementById('employee-modal'))
        })

        employeeForm.addEventListener('submit', function (event) {
            event.preventDefault()
            clearFormError(employeeForm)
            var data = {}
            new FormData(employeeForm).forEach(function (value, key) { data[key] = value || null })
            var employeeId = employeeForm.dataset.employeeId
            var url = employeeId
                ? employeeForm.dataset.updateUrl.replace('__employee__', encodeURIComponent(employeeId))
                : employeeForm.dataset.createUrl
            var button = employeeForm.querySelector('[type=submit]')
            button.disabled = true
            request(url, { method: employeeId ? 'PUT' : 'POST', body: JSON.stringify(data) })
                .then(function () {
                    showToast(employeeId ? 'Employee record updated.' : 'Employee added to the directory.')
                    closeModal(document.getElementById('employee-modal'))
                    employeeForm.reset()
                    delete employeeForm.dataset.employeeId
                    loadEmployeeDirectory(1)
                })
                .catch(function (error) { showFormError(employeeForm, error.message) })
                .then(function () { button.disabled = false })
        })
    }

    document.addEventListener('click', function (event) {
        var opener = event.target.closest('[data-modal-open]')
        if (opener) {
            var modal = document.getElementById(opener.dataset.modalOpen)
            if (modal && modal.id === 'employee-modal' && employeeForm) {
                employeeForm.reset()
                delete employeeForm.dataset.employeeId
                clearFormError(employeeForm)
                document.getElementById('employee-modal-title').textContent = 'Add an employee'
                employeeForm.querySelector('[type=submit]').textContent = 'Save employee'
            }
            openModal(modal)
        }

        var closer = event.target.closest('.modal-close, .modal-cancel')
        if (closer) closeModal(closer.closest('.modal-backdrop'))

        if (event.target.classList.contains('modal-backdrop')) closeModal(event.target)

        var employeeStatusButton = event.target.closest('.employee-status-toggle')
        if (employeeStatusButton) {
            var employeeName = employeeStatusButton.dataset.employeeName || 'this employee'
            var nextActive = employeeStatusButton.dataset.nextStatus === '1'
            var confirmation = window.Swal
                ? window.Swal.fire({
                    icon: 'warning',
                    title: (nextActive ? 'Activate' : 'Deactivate') + ' employee record?',
                    text: '"' + employeeName + '" will be ' + (nextActive ? 'available in the active employee directory.' : 'kept in the system but removed from the active directory.'),
                    showCancelButton: true,
                    confirmButtonText: nextActive ? 'Activate' : 'Deactivate',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: nextActive ? '#16734e' : '#aa3d44'
                }).then(function (result) { return result.isConfirmed })
                : Promise.resolve(window.confirm((nextActive ? 'Activate ' : 'Deactivate ') + employeeName + '?'))

            confirmation.then(function (confirmed) {
                if (!confirmed) return
                employeeStatusButton.disabled = true
                request(employeeStatusButton.dataset.statusUrl, {
                    method: 'PATCH',
                    body: JSON.stringify({ is_active: nextActive ? 1 : 0 })
                }).then(function (payload) {
                    var activeCounter = document.querySelector('[data-active-count]')
                    var inactiveCounter = document.querySelector('[data-inactive-count]')
                    var sourceCounter = nextActive ? inactiveCounter : activeCounter
                    var destinationCounter = nextActive ? activeCounter : inactiveCounter
                    sourceCounter.textContent = String(Math.max(0, Number(sourceCounter.textContent || 0) - 1))
                    destinationCounter.textContent = String(Number(destinationCounter.textContent || 0) + 1)
                    showToast(payload.message || 'Employee record status updated.')
                    loadEmployeeDirectory(Number(directoryPanel.dataset.page || 1))
                }).catch(function (error) {
                    employeeStatusButton.disabled = false
                    showToast(error.message, true)
                })
            })
        }

        var exportButton = event.target.closest('[data-export-table]')
        if (exportButton) {
            var table = document.querySelector('#' + exportButton.dataset.exportTable + ' table')
            if (!table) return
            var rows = Array.from(table.querySelectorAll('tr')).map(function (row) {
                return Array.from(row.querySelectorAll('th, td')).map(function (cell) {
                    return '"' + cell.innerText.trim().replace(/"/g, '""') + '"'
                }).join(',')
            })
            var blob = new Blob([rows.join('\r\n')], { type: 'text/csv;charset=utf-8;' })
            var link = document.createElement('a')
            link.href = URL.createObjectURL(blob)
            link.download = 'employees.csv'
            link.click()
            URL.revokeObjectURL(link.href)
        }
    })

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.modal-open').forEach(closeModal)
        }
    })
})()
