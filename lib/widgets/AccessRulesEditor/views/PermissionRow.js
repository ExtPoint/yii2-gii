import React from 'react';
import PropTypes from 'prop-types';
import Collapse from 'react-bootstrap/lib/Collapse';
import {connect} from 'react-redux';
import {formValueSelector} from 'redux-form';

import {html} from 'components';
import PermissionCheckbox from './PermissionCheckbox';

import './PermissionRow.less';

const bem = html.bem('PermissionRow');
const FORM_ID = 'AccessRulesEditor';
const selector = formValueSelector(FORM_ID);

@connect(
    (state, props) => {
        // Count child checked items
        let checkedCount = 0;
        const formRules = selector(state, 'rules') || {};
        const countChecked = function(permissionName) {
            Object.keys(formRules).map(role => {
                if (formRules[role][permissionName]) {
                    checkedCount++;
                }
            });

            const permission = props.permissions.find(permission => permission.name === permissionName);
            (permission.children || []).forEach(countChecked);
        };
        countChecked(props.permission.name);

        return {
            checkedCount,
        };
    }
)
export default class PermissionRow extends React.PureComponent {

    static propTypes = {
        roles: PropTypes.arrayOf(PropTypes.string),
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
        parentPermission: PropTypes.string,
        level: PropTypes.number,
        checkedCount: PropTypes.number,
    };

    static defaultProps = {
        level: 0,
    };

    constructor() {
        super(...arguments);

        this.state = {
            isExpanded: false,
        };
    }

    render() {
        const WrappedPermissionRow = exports.default;
        return (
            <div className={bem.block()}>
                <div className='row'>
                    <div className='col-xs-6 col-md-4'>
                        {this.props.permission.children && (
                            <a
                                href='javascript:void(0)'
                                className={bem.element('link')}
                                onClick={() => this.setState({isExpanded: !this.state.isExpanded})}
                                style={{
                                    marginLeft: 30 * this.props.level,
                                }}
                            >
                                <span
                                    className={bem(
                                        !this.state.isExpanded ? 'glyphicon glyphicon-plus' : 'glyphicon glyphicon-minus',
                                        bem.element('collapse-icon'),
                                    )}
                                />
                                <span className={bem.element('description')}>
                                    <code>
                                        {this.props.permission.description}
                                    </code>
                                    &nbsp;
                                    {this.props.checkedCount > 0 && (
                                        <span className='badge'>
                                            {this.props.checkedCount}
                                        </span>
                                    )}
                                </span>
                            </a>
                        ) ||
                        (
                            <div
                                className={bem.element('link')}
                                style={{
                                    marginLeft: 30 * this.props.level,
                                }}
                            >
                                <span className={bem.element('description')}>
                                    <code>
                                        {this.props.permission.description}
                                    </code>
                                </span>
                            </div>
                        )}
                    </div>
                    {this.props.roles.map(role => (
                        <div
                            key={role}
                            className='col-xs-1 text-center'
                        >
                            <PermissionCheckbox
                                permissions={this.props.permissions}
                                permission={this.props.permission}
                                role={role}
                            />
                        </div>
                    ))}
                </div>
                {this.props.permission.children && (
                    <Collapse in={this.state.isExpanded}>
                        <div>
                            {this.getChildren().map(children => (
                                <WrappedPermissionRow
                                    key={children.name}
                                    permission={children}
                                    roles={this.props.roles}
                                    permissions={this.props.permissions}
                                    level={this.props.level + 1}
                                />
                            ))}
                        </div>
                    </Collapse>
                )}
            </div>
        )
    }

    getChildren() {
        if (!this.props.permission.children) {
            return [];
        }
        return this.props.permissions.filter(permission => {
            return this.props.permission.children.indexOf(permission.name) !== -1;
        });
    }

}
