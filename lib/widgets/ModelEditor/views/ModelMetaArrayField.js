import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Field, formValueSelector} from 'redux-form';
import {arrayPush} from 'redux-form/lib/actions';
import _isArray from 'lodash/isArray';

import {html} from 'components';

import './ModelMetaArrayField.less';
const bem = html.bem('ModelMetaArrayField');

class ModelMetaArrayField extends React.Component {

    static formId = 'ModelEditor';

    static propTypes = {
        fields: PropTypes.object,
        appTypes: PropTypes.arrayOf(PropTypes.shape({
            name: PropTypes.string
        })),
        formValues: PropTypes.object,
        onKeyDown: PropTypes.func,
    };

    render() {
        return (
            <div className={bem(bem.block(), 'form-inline')}>
                <div className='pull-right text-muted'>
                    <small>Используйте <span className='label label-default'>Shift</span> + <span className='label label-default'>↑↓</span> для перехода по полям</small>
                </div>
                <h3>
                    Attributes meta information
                </h3>
                {this._renderDataLists()}
                <table className='table table-striped table-hover'>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Label</th>
                        <th>Hint</th>
                        <th className={bem.element('th-app-types')}>
                            Type
                        </th>
                        <th className={bem.element('th-small')}>
                            Required
                        </th>
                        <th className={bem.element('th-small')}>
                            Show in form
                        </th>
                        <th className={bem.element('th-small')}>
                            Show in filter
                        </th>
                        <th className={bem.element('th-small')}>
                            Show in table
                        </th>
                        <th className={bem.element('th-small')}>
                            Show in view
                        </th>
                        <th />
                    </tr>
                    </thead>
                    <tbody>
                    {this.props.fields.map((attribute, index) => (
                        <tr key={index}>
                            <td>
                                {index+1}
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[name]`}
                                    component='input'
                                    className='form-control input-sm'
                                    onKeyDown={this.props.onKeyDown}
                                />
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[label]`}
                                    component='input'
                                    className='form-control input-sm'
                                    onKeyDown={this.props.onKeyDown}
                                />
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[hint]`}
                                    component='input'
                                    className='form-control input-sm'
                                    onKeyDown={this.props.onKeyDown}
                                />
                            </td>
                            <td className={bem(bem.element('td-app-types'), 'form-inline')}>
                                <div className='form-group'>
                                    <Field
                                        name={`${attribute}[appType]`}
                                        component='select'
                                        className='form-control input-sm'
                                        onKeyDown={this.props.onKeyDown}
                                    >
                                        <option value=''></option>
                                        {this.props.appTypes.map(appType => (
                                            <option key={appType.name} value={appType.name}>{appType.title}</option>
                                        ))}
                                    </Field>
                                </div>
                                {this._renderAppTypeFields(index, attribute)}
                            </td>
                            <td>
                                <div className={bem(bem.element('td-checkbox'), 'checkbox')}>
                                    <label>
                                        {this.props.formValues.meta[index] && this.props.formValues.meta[index].appType === 'primaryKey' && (
                                            <input
                                                type='checkbox'
                                                checked={true}
                                                disabled={true}
                                            />
                                        ) || (
                                            <Field
                                                name={`${attribute}[required]`}
                                                component='input'
                                                type='checkbox'
                                                onKeyDown={this.props.onKeyDown}
                                            />
                                        )}
                                    </label>
                                </div>
                            </td>
                            {['showInForm', 'showInFilter', 'showInTable', 'showInView'].map(key => (
                                <td key={key}>
                                    <div className={bem(bem.element('td-checkbox'), 'checkbox')}>
                                        <label>
                                            <Field
                                                name={`${attribute}[${key}]`}
                                                component='input'
                                                type='checkbox'
                                                onKeyDown={this.props.onKeyDown}
                                            />
                                        </label>
                                    </div>
                                </td>
                            ))}
                            <td style={{width: 90, textAlign: 'right'}}>
                                {this._isShowButtonAddRelation(index) && (
                                    <button
                                        type='button'
                                        className={'btn btn-sm btn-primary'}
                                        onClick={() => this._addRelation(index)}
                                        title='Add hasOne relation'
                                    >
                                        <span className='glyphicon glyphicon-link' />
                                    </button>
                                )}
                                &nbsp;
                                <button
                                    type='button'
                                    className={'btn btn-sm btn-danger'}
                                    onClick={() => this.props.fields.remove(index)}
                                >
                                    <span className='glyphicon glyphicon-remove' />
                                </button>
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
                <div>
                    <a
                        className='btn btn-sm btn-default'
                        href='javascript:void(0)'
                        onClick={() => this.props.fields.push()}
                    >
                        <span className='glyphicon glyphicon-plus' /> Добавить
                    </a>
                </div>
            </div>
        );
    }

    _renderDataLists() {
        const lists = {};
        this.props.appTypes.forEach(appType => {
            Object.keys(appType.fieldProps).map(key => {
                const list = appType.fieldProps[key].list;
                switch (list) {
                    case 'relations':
                        lists[key] = this.props.formValues.relations.map(r => r && r.name).filter(Boolean);
                        break;

                    default:
                        if (_isArray(list)) {
                            lists[key] = list;
                        }
                }
            });
        });

        return Object.keys(lists).map(key => (
            <datalist key={key} id={`${ModelMetaArrayField.formId}_${key}`}>
                {lists[key].map(item => (
                    <option key={item} value={item} />
                ))}
            </datalist>
        ));
    }

    _renderAppTypeFields(index, attribute) {
        const appTypeName = this.props.formValues.meta[index] && this.props.formValues.meta[index].appType;
        const appType = this.props.appTypes.find(t => t.name === appTypeName);
        if (!appType) {
            return;
        }

        return Object.keys(appType.fieldProps).map(key => {
            const id = `${attribute}_${appType.name}_${key}_input`;
            const {label, list, options, ...props} = appType.fieldProps[key];

            const inputProps = {
                component: 'input',
                ...props,
                id,
                name: `${attribute}[${key}]`,
                list: list ? `${ModelMetaArrayField.formId}_${key}` : undefined,
                onKeyDown: this.props.onKeyDown
            };

            switch (props.component) {
                case 'select':
                    return (
                        <div key={key} className='form-group'>
                            <Field
                                {...inputProps}
                                className='form-control input-sm'
                                placeholder={label || appType.name}
                            >
                                {Object.keys(options || {}).map(key => (
                                    <option key={key} value={key}>{options[key]}</option>
                                ))}
                            </Field>
                        </div>
                    );

                case 'input':
                    switch (props.type) {
                        case 'checkbox':
                            return (
                                <div key={key} className='checkbox'>
                                    <label>
                                        <Field {...inputProps} />
                                        &nbsp;
                                        {label || appType.name}
                                    </label>
                                </div>
                            );
                    }
                    break;
            }

            return (
                <div key={key} className='form-group'>
                    <Field
                        {...inputProps}
                        placeholder={label || appType.name}
                        className='form-control input-sm'
                    />
                </div>
            );
        });
    }

    _addRelation(index) {
        const key = this.props.formValues.meta[index].name;
        this.props.dispatch(arrayPush(ModelMetaArrayField.formId, 'relations', {
            type: 'hasOne',
            name: key.replace('Id', ''),
            relationModelClassName: '',
            relationKey: 'id',
            selfKey: key,
        }));
    }

    _isShowButtonAddRelation(index) {
        const key = this.props.formValues.meta[index] && this.props.formValues.meta[index].name || '';
        return key.indexOf('Id') !== -1 && !this.props.formValues.relations.find(relation => {
            return relation && relation.type === 'hasOne' && relation.selfKey === key;
        });
    }

}

const selector = formValueSelector(ModelMetaArrayField.formId);
export default connect(
    state => ({
        formValues: {
            meta: selector(state, 'meta'),
            relations: selector(state, 'relations'),
        }
    })
)(ModelMetaArrayField);