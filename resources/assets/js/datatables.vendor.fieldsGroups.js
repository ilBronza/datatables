(function registerIlBronzaDatatablesFieldsGroups() {
    /**
     * Domain API for logical DataTables column groups.
     *
     * It deliberately knows nothing about UIkit dropdowns. Both the generic
     * fieldsGroups menu and declarative fieldsGroupToggle buttons use it.
     */
    const fieldsGroups = {
        resolveTableNode: function (dt) {
            if (!dt)
                return null;

            try {
                if (typeof dt.table === 'function')
                    return dt.table().node();
            } catch (err) {}

            try {
                if (typeof dt.settings === 'function') {
                    const settings = dt.settings();
                    return settings && settings[0] ? settings[0].nTable : null;
                }
            } catch (err) {}

            return null;
        },

        getTableId: function (dt) {
            const tableNode = this.resolveTableNode(dt);

            if (!tableNode)
                return null;

            return tableNode.id || tableNode.getAttribute('data-realid') || null;
        },

        getDefinitions: function (dt) {
            const tableId = this.getTableId(dt);

            if (!tableId)
                return {};

            return (window.__ibDatatableFieldsGroups && window.__ibDatatableFieldsGroups[tableId]) || {};
        },

        getGroupDefinition: function (dt, groupName) {
            if (!groupName)
                return null;

            return this.getDefinitions(dt)[String(groupName)] || null;
        },

        getGroupNames: function (dt) {
            return Object.keys(this.getDefinitions(dt)).sort(function (a, b) {
                return a.localeCompare(b);
            });
        },

        getHiddenState: function (dt) {
            const tableId = this.getTableId(dt);

            if (!tableId)
                return {};

            window.__ibDtFieldsGroupsHidden = window.__ibDtFieldsGroupsHidden || {};
            window.__ibDtFieldsGroupsHidden[tableId] = window.__ibDtFieldsGroupsHidden[tableId] || {};

            return window.__ibDtFieldsGroupsHidden[tableId];
        },

        collectColumnIndexes: function (dt, groupDefinition) {
            const indexes = Array.isArray(groupDefinition && groupDefinition.columnIndexes)
                ? groupDefinition.columnIndexes
                : [];
            const out = [];
            let columnCount = 0;

            try {
                columnCount = dt.columns().count();
            } catch (err) {
                return out;
            }

            indexes.forEach(function (columnIndex) {
                const parsedIndex = parseInt(columnIndex, 10);

                if (isNaN(parsedIndex) || parsedIndex < 0 || parsedIndex >= columnCount)
                    return;

                if (out.indexOf(parsedIndex) === -1)
                    out.push(parsedIndex);
            });

            return out;
        },

        fieldNameByColumnIndex: function (dt, columnIndex) {
            const tableId = this.getTableId(dt);

            if (!tableId)
                return null;

            const map = (window.__ibDatatableColumnIndexToFieldName || {})[tableId] || {};

            if (map[columnIndex] != null)
                return String(map[columnIndex]);
            if (map[String(columnIndex)] != null)
                return String(map[String(columnIndex)]);

            return null;
        },

        groupLooksFullyHidden: function (dt, groupDefinition) {
            const indexes = this.collectColumnIndexes(dt, groupDefinition);

            if (!indexes.length)
                return false;

            return indexes.every(function (columnIndex) {
                try {
                    return !dt.column(columnIndex).visible();
                } catch (err) {
                    return false;
                }
            });
        },

        isGroupActive: function (dt, groupName) {
            const groupDefinition = this.getGroupDefinition(dt, groupName);

            if (!groupDefinition)
                return false;

            const hiddenState = this.getHiddenState(dt);
            const hiddenByToggle = Array.isArray(hiddenState[groupName]) ? hiddenState[groupName] : [];

            const stillHiddenByToggle = hiddenByToggle.some(function (columnIndex) {
                try {
                    return !dt.column(columnIndex).visible();
                } catch (err) {
                    return false;
                }
            });

            return stillHiddenByToggle || this.groupLooksFullyHidden(dt, groupDefinition);
        },

        updateButtonState: function (dt, node, groupName, config) {
            const active = this.isGroupActive(dt, groupName);

            // DataTables Buttons hands a jQuery object to init/action in some
            // versions and a DOM node in others.
            const buttonNode = node && node.jquery ? node[0] : node;

            if (!buttonNode)
                return active;

            if (buttonNode.classList)
                buttonNode.classList.toggle('uk-button-primary', active);

            if (typeof buttonNode.setAttribute === 'function')
                buttonNode.setAttribute('aria-pressed', active ? 'true' : 'false');

            const stateText = active
                ? config && config.activeText
                : config && config.inactiveText;

            if (typeof stateText === 'string')
                buttonNode.innerHTML = stateText;

            return active;
        },

        setColumnsVisibility: function (dt, indexes, visibility) {
            const tableNode = this.resolveTableNode(dt);
            const columnDisplayRoute = tableNode && typeof tableNode.getAttribute === 'function'
                ? tableNode.getAttribute('data-columndisplayroute')
                : null;
            const fieldNamesToPersist = [];
            const api = this;

            indexes.forEach(function (columnIndex) {
                try {
                    dt.column(columnIndex).visible(visibility);
                } catch (err) {
                    return;
                }

                const fieldName = api.fieldNameByColumnIndex(dt, columnIndex);

                // applyOnly keeps the ordinary column-visibility sidebar in sync.
                if (fieldName && typeof dt.manageColumnVisibility === 'function') {
                    try {
                        dt.manageColumnVisibility(fieldName, visibility, {
                            silent: true,
                            mode: 'applyOnly',
                            forceColumnIndex: columnIndex,
                        });
                    } catch (err) {}
                }

                if (fieldName && columnDisplayRoute && typeof dt.manageColumnVisibility === 'function')
                    fieldNamesToPersist.push(fieldName);
            });

            let chain = Promise.resolve();

            fieldNamesToPersist.forEach(function (fieldName) {
                chain = chain.then(function () {
                    try {
                        return Promise.resolve(dt.manageColumnVisibility(fieldName, visibility, {
                            silent: true,
                            mode: 'persistOnly',
                        })).catch(function () {
                            return null;
                        });
                    } catch (err) {
                        return null;
                    }
                });
            });

            return chain;
        },

        toggle: function (dt, groupName) {
            const normalizedGroupName = typeof groupName === 'string' ? groupName.trim() : '';
            const groupDefinition = this.getGroupDefinition(dt, normalizedGroupName);

            if (!groupDefinition)
                return Promise.resolve({ changed: false, active: false });

            const hiddenState = this.getHiddenState(dt);
            const indexes = this.collectColumnIndexes(dt, groupDefinition);

            if (!indexes.length)
                return Promise.resolve({ changed: false, active: false });

            const hiddenByToggle = Array.isArray(hiddenState[normalizedGroupName])
                ? hiddenState[normalizedGroupName].filter(function (index) {
                    if (indexes.indexOf(index) === -1)
                        return false;

                    try {
                        return !dt.column(index).visible();
                    } catch (err) {
                        return false;
                    }
                })
                : [];
            const api = this;
            let visibility;
            let affectedIndexes;

            // A different group can legitimately expand an overlapping column.
            // Do not keep an obsolete restore marker in that case.
            hiddenState[normalizedGroupName] = hiddenByToggle;

            // Restore precisely the columns hidden by this toggle. Columns which
            // were already hidden before the first click are never stored here.
            if (hiddenByToggle.length) {
                hiddenState[normalizedGroupName] = [];
                visibility = true;
                affectedIndexes = hiddenByToggle;
            } else {
                affectedIndexes = indexes.filter(function (columnIndex) {
                    try {
                        return dt.column(columnIndex).visible();
                    } catch (err) {
                        return false;
                    }
                });

                if (affectedIndexes.length) {
                    hiddenState[normalizedGroupName] = affectedIndexes.slice();
                    visibility = false;
                } else {
                    // All columns were already hidden (for example by saved
                    // preferences): the first click expands the group.
                    hiddenState[normalizedGroupName] = [];
                    affectedIndexes = indexes;
                    visibility = true;
                }
            }

            return this.setColumnsVisibility(dt, affectedIndexes, visibility).then(
                function () {
                    try { dt.columns.adjust(); } catch (err) {}

                    return {
                        changed: affectedIndexes.length > 0,
                        active: api.isGroupActive(dt, normalizedGroupName),
                    };
                },
                function () {
                    try { dt.columns.adjust(); } catch (err) {}

                    return {
                        changed: affectedIndexes.length > 0,
                        active: api.isGroupActive(dt, normalizedGroupName),
                    };
                }
            );
        },
    };

    window.ibDatatableFieldsGroups = fieldsGroups;

    if (!($.fn && $.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.buttons))
        return;

    $.fn.dataTable.ext.buttons.fieldsGroupToggle = {
        className: 'ib-dt-fields-group-toggle',
        init: function (dt, node, config) {
            const groupName = config && config.fieldGroup;

            fieldsGroups.updateButtonState(dt, node, groupName, config);

            // Keep the visual state aligned when a user changes a column from
            // the normal visibility control or another grouped button.
            if (dt && typeof dt.on === 'function') {
                dt.on('column-visibility.dt', function () {
                    fieldsGroups.updateButtonState(dt, node, groupName, config);
                });
            }
        },
        action: function (e, dt, node, config) {
            const groupName = config && config.fieldGroup;

            return fieldsGroups.toggle(dt, groupName).then(function () {
                fieldsGroups.updateButtonState(dt, node, groupName, config);
            });
        },
    };
})();
