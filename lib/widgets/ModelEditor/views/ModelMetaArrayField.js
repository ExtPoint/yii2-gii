import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Field, formValueSelector} from 'redux-form';
import {arrayPush} from 'redux-form/lib/actions';

import {html} from 'components';

import './ModelMetaArrayField.less';
const bem = html.bem('ModelMetaArrayField');

class ModelMetaArrayField extends React.Component {

    static formId = 'ModelEditor';

    static propTypes = {
        fields: PropTypes.object,
        dbTypes: PropTypes.arrayOf(PropTypes.string),
        fieldWidgets: PropTypes.arrayOf(PropTypes.shape({
            name: PropTypes.string
        })),
        formatters: PropTypes.arrayOf(PropTypes.shape({
            name: PropTypes.string
        })),
        formValues: PropTypes.object,
    };

    render() {
        return (
            <div className={bem(bem.block(), 'form-inline')}>
                <h3>
                    Attributes meta information
                </h3>
                <datalist id={`${ModelMetaArrayField.formId}_dbTypeList`}>
                    {this.props.dbTypes.map(value => (
                        <option key={value} value={value} />
                    ))}
                </datalist>
                <datalist id={`${ModelMetaArrayField.formId}_fieldWidgetList`}>
                    {this.props.fieldWidgets.map(value => (
                        <option key={value.name} value={value.name} />
                    ))}
                </datalist>
                <datalist id={`${ModelMetaArrayField.formId}_formatterList`}>
                    {this.props.formatters.map(value => (
                        <option key={value.name} value={value.name} />
                    ))}
                </datalist>
                <table className='table table-striped table-hover'>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Label</th>
                        <th>Hint</th>
                        <th>DB Type</th>
                        <th className={bem.element('th-small')}>
                            Not Null
                        </th>
                        <th>Field Widget</th>
                        <th className={bem.element('th-small')}>
                            Show in form
                        </th>
                        <th>Formatter</th>
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
                                />
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[label]`}
                                    component='input'
                                    className='form-control input-sm'
                                />
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[hint]`}
                                    component='input'
                                    className='form-control input-sm'
                                />
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[dbType]`}
                                    component='input'
                                    className='form-control input-sm'
                                    placeholder='string'
                                    list={`${ModelMetaArrayField.formId}_dbTypeList`}
                                />
                            </td>
                            <td>
                                <div className={bem(bem.element('td-checkbox'), 'checkbox')}>
                                    <label>
                                        <Field
                                            name={`${attribute}[notNull]`}
                                            component='input'
                                            type='checkbox'
                                        />
                                    </label>
                                </div>
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[fieldWidget]`}
                                    component='input'
                                    className='form-control input-sm'
                                    placeholder='textInput'
                                    list={`${ModelMetaArrayField.formId}_fieldWidgetList`}
                                />
                            </td>
                            <td>
                                <div className={bem(bem.element('td-checkbox'), 'checkbox')}>
                                    <label>
                                        <Field
                                            name={`${attribute}[showInForm]`}
                                            component='input'
                                            type='checkbox'
                                        />
                                    </label>
                                </div>
                            </td>
                            <td>
                                <Field
                                    name={`${attribute}[formatter]`}
                                    component='input'
                                    className='form-control input-sm'
                                    placeholder='text'
                                    list={`${ModelMetaArrayField.formId}_formatterList`}
                                />
                            </td>
                            <td>
                                <div className={bem(bem.element('td-checkbox'), 'checkbox')}>
                                    <label>
                                        <Field
                                            name={`${attribute}[showInTable]`}
                                            component='input'
                                            type='checkbox'
                                        />
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div className={bem(bem.element('td-checkbox'), 'checkbox')}>
                                    <label>
                                        <Field
                                            name={`${attribute}[showInView]`}
                                            component='input'
                                            type='checkbox'
                                        />
                                    </label>
                                </div>
                            </td>
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
        const key = this.props.formValues.meta[index].name;
        return key.indexOf('Id') !== -1 && !this.props.formValues.relations.find(relation => {
            return relation.type === 'hasOne' && relation.selfKey === key;
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