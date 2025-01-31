export default class ZkDataGrid {

    gridObj = null;
    container = null;
    context = null;
    subscribers = {};
    subscribersData = {};
    isLazyLoad = false;

    options = {
        loading: `<div class="zk-datagrid-loading">Loading...</div>`,
        empty: `<div class="zk-datagrid-empty">No records found</div>`,
        loadingDelay: 0,
        class: 'zk-datagrid row',
        dataLoader: `<div class="zk-datagrid-loader">Loading...</div>`,
    }

    constructor(options = {}) {
        this.setOptions(options);
        this.context = document.createDocumentFragment();
    }

    lazyLoad(status = true) {
        this.isLazyLoad = status;
    }

    setGrid(grid) {
        this.context = (typeof grid === 'string') ? (document.getElementById(grid) || document.querySelector(grid)) : grid;
        if (!this.context) {
            console.error('Grid not found');
            return;
        }
        // Check if container is not set then set parent element as container
        if (!this.container) {
            this.container = this.context.parentElement;
        }
        this.registerEvents(this.isLazyLoad);
    }

    setOptions(options) {
        this.options = { ...this.options, ...options };
    }

    getOptions() {
        return this.options;
    }

    setGridObject(gridObj) {
        this.gridObj = (typeof gridObj === 'string') ? JSON.parse(gridObj) : gridObj;
        // Check if lazy load is enabled
        this.isLazyLoad = this.gridObj?.ajax || (this.gridObj?.lazyLoad || this.isLazyLoad);
    }

    setContainer(container) {

        // Attempt to find the container by ID or as a CSS selector
        this.container =
            typeof container === 'string'
                ? document.getElementById(container) || document.querySelector(container)
                : container;

        if (!this.container) {
            console.error('Container not found');
            return null;
        }

        return this.container;
    }

    render(container, gridObj) {
        if (container && !this.setContainer(container)) {
            this.dispatch('beforeRender', { status: 'error', message: 'Container not found' });
            return;
        }

        if (!this.container) {
            this.dispatch('beforeRender', { status: 'error', message: 'Container not found' });
            return;
        }

        if (gridObj) this.setGridObject(gridObj);

        const { baseUrl } = this.gridObj;

        this.generate();

        if (this.isLazyLoad) {
            this.requestHandler('link', baseUrl);
        }
    }

    generate(gridObj) {

        if (gridObj) this.setGridObject(gridObj);

        this.dispatch('beforeRender', { status: 'success', container: this.container, gridObj: this.gridObj });

        this.context = document.createDocumentFragment();

        // Clear Container
        this.container.innerHTML = '';

        // Container Loading
        this.container.appendChild(this.stringToHTML(this.options.loading));

        this.buildGrid();

        // Append to container
        this.container.innerHTML = '';
        this.container.appendChild(this.context);

        // Register Events
        this.registerEvents(this.isLazyLoad);

        this.dispatch('afterRender', { status: 'success', container: this.container });

        // Free up memory
        this.gridObj = null;
    }

    buildGrid() {
        if (!this.gridObj) {
            this.dispatch('beforeBuildGrid', { status: 'error', message: 'Grid object not found' });
            console.error('Grid object not found');
            return;
        }
        const div = document.createElement('div');
        div.id = `grid-${this.gridObj.uid}`;
        div.className = this.options.class;
        this.context = div;

        this.dispatch('beforeBuildGrid', { status: 'success', context: this.context });

        // Build Form
        this.buildForm(this.context);

        // Build Search
        this.buildSearch(this.context);

        // Build Mass Actions
        this.buildMassActions(this.context);

        // Build Table
        this.buildDataTable(this.context);

        this.dispatch('afterBuildGrid', { status: 'success', context: this.context });
    }

    buildForm(context) {
        let template = this.options.formTemplate || this.getFormTemplate();

        if (typeof template === 'function') {
            template = template(this.gridObj);
        }

        if (template) {
            context.appendChild(this.stringToHTML(template));
        }
    }

    buildSearch(context) {

        let template = this.options.searchTemplate || this.getSearchTemplate();

        if (typeof template === 'function') {
            template = template(this.gridObj);
        }

        if (template) {
            context.appendChild(this.stringToHTML(template));
        }
    }

    buildMassActions(context) {
        if (!this.gridObj?.massActions || !this.gridObj?.massActions.length) return;

        let template = this.options.massActionsTemplate || this.getMassActionsTemplate();

        if (typeof template === 'function') {
            template = template(this.gridObj);
        }

        if (template) {
            context.appendChild(this.stringToHTML(template));
        }
    }

    buildDataTable(context) {

        let template = this.options.dataTableTemplate || this.getDataTableTemplate();

        if (typeof template === 'function') {
            template = template(this.gridObj);
        }

        if (template) {
            context.appendChild(this.stringToHTML(template));
        }
    }

    stringToHTML(html) {
        if (!html) return document.createDocumentFragment();
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        return template.content;
    }

    subscribe(eventName, callback, lastData = false) {
        this.subscribers[eventName] = this.subscribers[eventName] || [];
        this.subscribers[eventName].push(callback);

        // Call the callback with the last data
        // if (lastData && this.subscribersData[eventName]) {
        //     callback(this.subscribersData[eventName]);
        // }
    }

    unsubscribe(eventName, callback) {
        if (!this.subscribers[eventName]) return;
        this.subscribers[eventName] = this.subscribers[eventName].filter(subscriber => subscriber !== callback);
    }

    dispatch(eventName, data) {
        // Set the last data for the event
        // this.subscribersData[eventName] = data;
        if (!this.subscribers[eventName]) return;
        this.subscribers[eventName].forEach(subscriber => subscriber(data));
    }

    getFormTemplate() {

        const { uid, baseUrl, data } = this.gridObj;
        const { requestQuery } = data;

        const renderHiddenInputs = (data, parentKey = '') => {
            const skipKeys = new Set(['page', 'limit', 'search', 'filters']);
            return Object.entries(data)
                .filter(([key]) => !skipKeys.has(key))
                .map(([key, value]) => {
                    const inputName = parentKey ? `${parentKey}[${key}]` : key;
                    return typeof value === 'object' ?
                        renderHiddenInputs(value, inputName) :
                        `<input type="hidden" name="${inputName}" value="${value}">`;
                }).join('');
        }

        return `<form class="grid-form" id="frm-${uid}" method="GET" action="${baseUrl}">${renderHiddenInputs(requestQuery)}</form>`;
    }

    getSearchTemplate() {

        const { uid } = this.gridObj;
        const { perPageOptions, limit, hasSearch, search } = this.gridObj.data;

        return `
            <div class="col-12 mb-2 grid-input">
                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-3 mb-2 mb-md-0">
                        <div class="input-group">
                            <label for="grid-limit-${uid}" class="input-group-text">Display</label>
                            <select class="form-select grid-change" name="limit" id="grid-limit-${uid}">
                                ${perPageOptions.map((option) => `<option value="${option}" ${option === limit ? "selected" : ""}>${option}</option>`).join("")}
                            </select>
                            <label for="grid-limit-${uid}" class="input-group-text">results</label>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-7 offset-lg-4 col-lg-5">
                    ${hasSearch ? `
                        <div class="input-group">
                            <input type="text" placeholder="Search" class="form-control" name="search" value="${search || ''}">
                            <button class="btn btn-outline-secondary btn-grid-search" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                                </svg>
                            </button>
                        </div>
                    `: ``}
                    </div>
                </div>
            </div>
        `;
    }

    getMassActionsTemplate() {

        const { uid, csrf_token, massActions } = this.gridObj;

        const optionsHtml = massActions.map((action) => {
            if (action.options && action.options.length) {
                // Render optgroup with nested options
                const optgroupOptions = action.options.map((option) => `
                    <option data-url="${option.url || action.url || ''}" data-method="${option.method || action.method || ''}" data-params='${JSON.stringify(option.attributes || action.attributes || {})}' value="${option.value}">
                        ${option.label}
                    </option>
                `).join('');
                return `<optgroup label="${action.title}">${optgroupOptions}</optgroup>`;
            } else {
                // Render single option
                return `
                    <option data-url="${action.url || ''}" data-method="${action.method || ''}" data-params='${JSON.stringify(action.attributes || {})}' value="${action.index}">
                        ${action.title}
                    </option>
                `;
            }
        }).join('');

        return `
            <div class="col-12 mb-2">
                <form class="grid-mass-action-form" id="frm-mass-action-${uid}">
                    <input type="hidden" name="_token" value="${csrf_token}" autocomplete="off">
                </form>
                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-3 mb-2 mb-md-0">
                        <div class="input-group">
                            <select class="form-select mass-action-input">
                                <option value="">Select action</option>
                                ${optionsHtml}
                            </select>
                            <button class="btn btn-outline-secondary btn-mass-action" type="button">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    getDataTableTemplate() {

        const { uid, columns, massActions, actions, data: { key, items, hasPages } } = this.gridObj;

        return `
            <div class="col-12 mb-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                ${massActions?.length ? `
                                    <th class="mass-action align-middle">
                                        <div class="form-check form-check-inline m-0 px-2">
                                            <input style="width: 18px;" class="form-check select-all-input" type="checkbox">
                                        </div>
                                    </th>
                                ` : ''}
                                ${columns.map((column) => this.getColumnTemplate(column)).join('')}
                                ${actions?.length ? `<th class="row-action align-top">Actions</th>` : ''}
                            </tr>
                        </thead>
                        <tbody class="grid-items">
                            ${items?.length ? items.map((item) => `
                                    <tr class="grid-row">
                                        ${massActions && massActions.length ? `
                                            <td class="mass-action align-middle">
                                                <div class="form-check form-check-inline m-0 px-2">
                                                    <input style="width: 16px;" class="form-check mass-row-input" type="checkbox" name="selected[]" value="${item[key]}">
                                                </div>
                                            </td>
                                        ` : ''}
                                        ${columns.map((column) => this.getItemTemplate(column, item)).join('')}
                                        ${item.actions?.length ? `
                                            <td class="row-action align-top">
                                                ${item.actions.map((action) => this.getActionTemplate(action, item)).join('')}
                                            </td>
                                        ` : ''}
                                    </tr>
                                `).join('') : ''}
                            <tr class="grid-empty-data" ${items?.length ? 'style="display: none;"' : ''}>
                                <td colspan="${columns.length + (massActions?.length ? 1 : 0) + (actions?.length ? 1 : 0)}">
                                    ${this.options.empty}
                                </td>
                            </tr>
                            <tr class="grid-data-loader" style="display: none;">
                                <td colspan="${columns.length + (massActions?.length ? 1 : 0) + (actions?.length ? 1 : 0)}">
                                    ${this.options.dataLoader}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            ${hasPages ? this.getPaginationTemplate() : ''}
        `;
    }

    getColumnTemplate(column) {

        let template = this.options.columnTemplate || null;
        if (typeof template === 'function') {
            template = template(column, this.gridObj);
        }
        if (template) return template;

        const { baseUrl, data: { sort, order } } = this.gridObj;
        const { index, title, sortable, column: col, filterable } = column;

        return `
            <th class="align-top grid-column${filterable ? ' grid-filter' : ''}" data-index="${index}" data-sortable="${sortable ? 'true' : 'false'}" ${sort == col ? `data-order="${order}"` : ''}>
                ${sortable ? `<a href="${baseUrl}${column.sortableLink}" class="column-sort-link d-block">${title}</a>` : title}
                ${filterable ? this.getFilterTemplate(column) : ''}
            </th>
        `;

    }

    getFilterTemplate(column) {

        let template = this.options.filterTemplate || null;
        if (typeof template === 'function') {
            template = template(column, this.gridObj);
        }
        if (template) return template;

        const { index, options: { attributes = {}, type, options: filterOptions } } = column;

        const filterValue = this.gridObj.data.filters[index] ?? '';

        let inputClass = 'form-control';
        // Use switch to set the class based on the input type
        switch (type) {
            case 'select':
            case 'multiselect':
                inputClass = 'form-select';
                break;
            case 'radio':
            case 'checkbox':
                inputClass = 'form-check-input';
                break;
        }
        attributes.class = attributes.class ?? inputClass;
        if (type === 'multiselect') attributes.multiple = 'multiple';
        const attributesString = Object.keys(attributes).map((key) => {
            const val = (key == 'class') ? `${attributes[key]} grid-filter-input` : attributes[key];
            return `${key}="${val}"`;
        }).join(' ');

        let filterInput = '';
        switch (type) {
            case 'select':
            case 'multiselect':
                const _filterValue = Array.isArray(filterValue) ? filterValue : filterValue.split(',');
                filterInput = `
                    <select ${attributesString} name="filters[${index}]${type == 'multiselect' ? '[]' : ''}">
                        ${filterOptions.map((option) => `<option value="${option.value}" ${_filterValue.includes(option.value) ? 'selected' : ''}>${option.label}</option>`).join('')}
                    </select>
                `;
                break;
            case 'radio':
                filterInput = filterOptions.map((option) => `
                    <div class="form-check form-check-inline">
                        <input ${attributesString} type="radio" name="filters[${index}]" value="${option.value}" ${filterValue == option.value ? 'checked' : ''}>
                        <label class="form-check-label">${option.label}</label>
                    </div>
                `).join('');
                break;
            case 'checkbox':
                const _filterCheckValue = Array.isArray(filterValue) ? filterValue : filterValue.split(',');
                filterInput = filterOptions.map((option) => `
                    <div class="form-check form-check-inline">
                        <input ${attributesString} type="checkbox" value="${option.value}" name="filters[${index}]${filterOptions.length > 1 ? '[]' : ''}" ${_filterCheckValue.includes(option.value) ? 'checked' : ''}>
                        <label class="form-check-label">${option.label}</label>
                    </div>
                `).join('');
                break;
            default:
                filterInput = `<input ${attributesString} type="${type}" name="filters[${index}]" value="${filterValue}">`;
                break;
        }

        return `
            <div class="mt-2 input-group input-${type}">
                ${filterInput}
                <button class="btn btn-outline-secondary btn-grid-filter" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </button>
            </div>
        `;
    }

    getItemTemplate(column, item) {

        let template = this.options.itemTemplate || null;
        if (typeof template === 'function') {
            template = template(column, item, this.gridObj);
        }
        if (template) return template;

        return `<td data-index="${column.index}">${item[column.column]}</td>`;
    }

    getActionTemplate(action, item) {

        let template = this.options.actionTemplate || null;
        if (typeof template === 'function') {
            template = template(action, item, this.gridObj);
        }
        if (template) return template;

        const { method, formatter, attributes = {} } = action;
        const { confirm } = attributes;

        if (formatter) return formatter;

        switch (method) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                return `
                    <form action="${action.url}" method="POST" style="display: inline;" ${confirm ? `onsubmit="return confirm('${confirm}');"` : ''}>
                        <input type="hidden" name="_token" value="${this.gridObj.csrf_token}" autocomplete="off">
                        ${!['POST', 'GET'].includes(method) ? `<input type="hidden" name="_method" value="${method}">` : ''}
                        <button type="submit" class="btn btn-md btn-${method === 'DELETE' ? 'danger' : 'primary'}">
                            ${action.icon ? (action.formatIcon ? action.icon : `<i class="${action.icon}"></i>`) : ''}
                            ${action.title}
                        </button>
                    </form>
                `;
            default:
                return `
                    <a href="${action.url}" class="btn btn-md btn-primary">
                        ${action.icon ? (action.formatIcon ? action.icon : `<i class="${action.icon}"></i>`) : ''}
                        ${action.title}
                    </a>
                `;
        }
    }

    getPaginationTemplate() {

        const { baseUrl, data: { links, start, end, total } } = this.gridObj;

        return `
            <div class="col-12 mb-2 grid-pagination">
                <div class="row align-items-center">
                    <div class="col-sm-12 col-md-5 mb-2 mb-md-0">
                        Showing ${start ?? 0} to ${end ?? 0} of ${total} entries
                    </div>
                    <div class="col-sm-12 col-md-7">
                        <ul class="pagination flex-wrap justify-content-end mb-0">
                            ${links.map((page) => `
                                <li class="page-item ${!page.url ? 'disabled' : ''} ${page.active ? 'active' : ''}">
                                    ${page.url && !page.active ? `<a class="page-link grid-page-link" href="${baseUrl}${page.url}">${page.label}</a>` : `<span class="page-link">${page.label}</span>`}
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                </div>
            </div>
        `;
    }

    removeInput(selector) {
        const input = this.context?.querySelectorAll(selector);
        if (input) {
            input.forEach((el) => el.remove());
        }
    }

    createInput(name, value, className = 'grid-data-input') {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        input.className = className;
        return input;
    }

    addInputToForm(selector) {
        const form = this.context?.querySelector('.grid-form');
        const inputs = this.context?.querySelectorAll(selector);
        if (form && inputs) {
            inputs.forEach((input) => {
                let value = input.value;
                const isMultiple = input.tagName === 'SELECT' && input.multiple;
                if (isMultiple) {
                    value = Array.from(input.selectedOptions).map(option => option.value);
                }
                if (
                    ((input.type === 'checkbox' || input.type === 'radio') && !input.checked) ||
                    value === ''
                ) {
                    return;
                }
                if (value instanceof Array) {
                    value.forEach((val) => form.appendChild(this.createInput(input.name, val)));
                } else {
                    form.appendChild(this.createInput(input.name, value));
                }
            });
        }
    }

    onSubmit(isLazy = false) {

        this.removeInput('.grid-form .grid-data-input');
        this.addInputToForm('.grid-input input, .grid-input select');
        this.addInputToForm('.grid-filter .grid-filter-input');
        if (!isLazy) {
            this.context?.querySelector('.grid-form')?.submit();
            return;
        }
        this.requestHandler('form');
    }

    requestHandler(type = 'form', url = null) {
        const { context } = this;
        if (!context) return;

        context.classList.add('grid-loading');

        const loader = context.querySelector('.grid-items .grid-data-loader');
        const emptyData = context.querySelector('.grid-items .grid-empty-data');
        const gridRows = context.querySelectorAll('.grid-items .grid-row');

        loader?.style.removeProperty('display');
        emptyData?.style.setProperty('display', 'none');

        // Clear existing grid rows
        gridRows.forEach((el) => el.remove());

        let fetchRequest;

        if (type === 'form') {
            const form = context.querySelector('.grid-form');
            if (!form) return;

            const formUrl = form.getAttribute('action');
            const method = form.getAttribute('method') || 'GET';
            const data = new FormData(form);

            fetchRequest = method === 'GET'
                ? fetch(`${formUrl}${formUrl.includes('?') ? '&' : '?'}${new URLSearchParams(data).toString()}`)
                : fetch(formUrl, { method, body: data });
        } else if (url) {
            fetchRequest = fetch(url);
        } else {
            console.log(url);
            console.error('No URL provided for requestHandler');
            return;
        }

        fetchRequest
            .then((res) => {
                return res.json();
            })
            .then((data) => this.generate(data))
            .catch((err) => {
                emptyData?.style.removeProperty('display');
                this.dispatch('requestError', { status: 'error', error: err });
            })
            .finally(() => {
                context.classList.remove('grid-loading');
                loader?.style.setProperty('display', 'none');
            });
    }

    registerEvents(isLazy = false) {

        this.context?.querySelectorAll('.btn-grid-search, .grid-filter .btn-grid-filter')
            ?.forEach((el) => el.addEventListener('click', () => this.onSubmit(isLazy)));

        this.context?.querySelectorAll('.grid-change')
            ?.forEach((el) => el.addEventListener('change', () => this.onSubmit(isLazy)));

        this.context?.querySelectorAll('.grid-input input, .grid-filter input.grid-filter-input')
            ?.forEach((el) => el.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.onSubmit(isLazy);
                }
            }));

        if (isLazy) {
            this.context?.querySelectorAll('.grid-column .column-sort-link')
                ?.forEach((el) => el.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.requestHandler('sort', e.target.getAttribute('href'));
                }));

            this.context?.querySelectorAll('.grid-pagination .grid-page-link')
                ?.forEach((el) => el.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.requestHandler('page', e.target.getAttribute('href'));
                }));
        }

        this.context?.querySelectorAll('.mass-action .select-all-input')
            ?.forEach((el) => el.addEventListener('change', (e) => {
                this.context?.querySelectorAll('.mass-action .mass-row-input')
                    ?.forEach((el) => el.checked = e.target.checked);
            }));

        this.context?.querySelector('.btn-mass-action')
            ?.addEventListener('click', async () => {
                const form = this.context?.querySelector('.grid-mass-action-form');
                if (!form) return;
                this.removeInput('.grid-mass-action-form .mass-action-data-input');
                const action = this.context?.querySelector('.mass-action-input');
                if (!action || !action.value) return;
                const selected = this.context?.querySelectorAll('.mass-action .mass-row-input:checked');
                if (!selected || !selected.length) return;

                form.action = action.options[action.selectedIndex].getAttribute('data-url');
                const method = action.options[action.selectedIndex].getAttribute('data-method');
                if (method) {
                    form.method = (method === 'GET') ? 'GET' : 'POST';
                    if (method !== 'GET' && method !== 'POST') {
                        form.appendChild(this.createInput('_method', method, 'mass-action-data-input'));
                    }
                }
                // Add Selected Items
                await selected.forEach((el) => {
                    form.appendChild(this.createInput('selected[]', el.value, 'mass-action-data-input'));
                });

                // Add Additional Params
                let isConfirmMessage = false;
                const params = action.options[action.selectedIndex].getAttribute('data-params');
                if (params) {
                    const data = JSON.parse(params);
                    if (data.confirm) {
                        isConfirmMessage = data.confirm;
                    }
                }
                if (isConfirmMessage) {
                    if (confirm(isConfirmMessage)) {
                        form.submit();
                    } else {
                        this.removeInput('.grid-mass-action-form .mass-action-data-input');
                    }
                } else {
                    form.submit();
                }
            });
    }
}
