import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Field, change} from 'redux-form';

import {html} from 'components';

import './PermissionCheckbox.less';

const bem = html.bem('PermissionCheckbox');
const FORM_ID = 'AccessRulesEditor';

@connect()
export default class PermissionCheckbox extends React.PureComponent {

    static propTypes = {
        role: PropTypes.string,
        permissions: PropTypes.arrayOf(PropTypes.shape({
            name: PropTypes.string,
            description: PropTypes.string,
            children: PropTypes.arrayOf(PropTypes.string),
        })),
        permission: PropTypes.shape({
            name: PropTypes.string,
            description: PropTypes.string,
            children: PropTypes.arrayOf(PropTypes.string),
        }),
    };

    constructor() {
        super(...arguments);

        this._onChange = this._onChange.bind(this);
    }

    render() {
        return (
            <div
                key={this.props.permission.name}
                className={bem.element('checkbox')}
            >
                <label>
                    <Field
                        name={`rules[${this.props.role}][${this.props.permission.name}]`}
                        component='input'
                        type='checkbox'
                        onClick={this._onChange}
                    />
                </label>
            </div>
        );
    }

    _onChange(e) {
        const isChecked = e.target.checked;

        // Change children
        this.props.dispatch(this.getChildrenNamesRecursive(this.props.permission.name).map(name => {
            return change(FORM_ID, `rules[${this.props.role}][${name}]`, isChecked);
        }));

        // Uncheck parent
        if (!isChecked) {
            this.props.dispatch(this.getParentNamesRecursive(this.props.permission.name).map(name => {
                return change(FORM_ID, `rules[${this.props.role}][${name}]`, false);
            }));
        }
    }

    /**
     * @param {string} permissionName
     * @returns {string[]}
     */
    getChildrenNamesRecursive(permissionName) {
        const permission = this.props.permissions.find(permission => permission.name === permissionName);
        const names = [].concat(permission.children || []);
        names.forEach(childrenName => {
            names.push(...this.getChildrenNamesRecursive(childrenName));
        });
        return names;
    }

    /**
     * @param {string} permissionName
     * @returns {string[]}
     */
    getParentNamesRecursive(permissionName) {
        const names = [];
        const parentPermission = this.props.permissions.find(permission => {
            return (permission.children || []).indexOf(permissionName) !== -1;
        });
        if (parentPermission) {
            names.push(parentPermission.name);
            names.push(...this.getParentNamesRecursive(parentPermission.name));
        }
        return names;

    }

}
