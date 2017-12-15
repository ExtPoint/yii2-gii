import React from 'react';
import PropTypes from 'prop-types';
import {reduxForm} from 'redux-form';

import {html, backendWidget} from 'components';
import PermissionRow from './views/PermissionRow';

import './AccessRulesEditor.less';

const bem = html.bem('AccessRulesEditor');
const FORM_ID = 'AccessRulesEditor';

@backendWidget.register('\\extpoint\\yii2\\gii\\widgets\\AccessRulesEditor\\AccessRulesEditor')
@reduxForm({
    form: FORM_ID,
})
export default class AccessRulesEditor extends React.PureComponent {

    static propTypes = {
        roles: PropTypes.arrayOf(PropTypes.string),
        permissions: PropTypes.arrayOf(PropTypes.shape({
            name: PropTypes.string,
            description: PropTypes.string,
            children: PropTypes.arrayOf(PropTypes.string),
        })),
        csrfToken: PropTypes.string,
    };

    render() {
        return (
            <form
                method='post'
                className={bem(bem.block(), 'form-horizontal')}
            >
                <input
                    type='hidden'
                    name='_csrf'
                    value={this.props.csrfToken}
                />
                <div className={bem.element('roles')}>
                    <div className='row'>
                        <div className='col-xs-6 col-md-4'>
                        </div>
                        {this.props.roles.map(role => (
                            <div
                                key={role}
                                className={bem(bem.element('roles'), 'col-xs-1')}
                            >
                                {role}
                            </div>
                        ))}
                    </div>
                </div>
                <div className={bem.element('permissions')}>
                    {this.getRoots().map(permission => (
                        <PermissionRow
                            key={permission.name}
                            permission={permission}
                            roles={this.props.roles}
                            permissions={this.props.permissions}
                        />
                    ))}
                </div>
                <div className='form-group'>
                    <div className='col-xs-12'>
                        <button
                            type='submit'
                            className='btn btn-success'
                        >
                            Сохранить
                        </button>
                    </div>
                </div>
            </form>
        );
    }

    getRoots() {
        const children = [];
        this.props.permissions.forEach(permission => {
            if (permission.children) {
                children.push(...permission.children);
            }
        });

        return this.props.permissions.filter(permission => {
            return children.indexOf(permission.name) === -1;
        });
    }

}