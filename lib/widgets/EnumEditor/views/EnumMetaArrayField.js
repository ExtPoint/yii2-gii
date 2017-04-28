import React from 'react';
import PropTypes from 'prop-types';
import {Field} from 'redux-form';

import {html} from 'components';

import './EnumMetaArrayField.less';
const bem = html.bem('EnumMetaArrayField');

class EnumMetaArrayField extends React.Component {

    static formId = 'EnumEditor';

    static propTypes = {
        fields: PropTypes.object,
        onKeyDown: PropTypes.func,
    };

    render() {
        return (
            <div className={bem(bem.block(), 'form-inline')}>
                <div className='pull-right text-muted'>
                    <small>Используйте <span className='label label-default'>Shift</span> + <span className='label label-default'>↑↓</span> для перехода по полям</small>
                </div>
                <h3>
                    Enum meta information
                </h3>
                <table className='table table-striped table-hover'>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Label</th>
                        <th>Css class</th>
                        <th />
                    </tr>
                    </thead>
                    <tbody>
                    {this.props.fields.map((attribute, index) => (
                        <tr key={index}>
                            <td>
                                {index + 1}
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
                                    name={`${attribute}[cssClass]`}
                                    component='input'
                                    className='form-control input-sm'
                                    list={`${EnumMetaArrayField.formId}_cssClassList`}
                                    onKeyDown={this.props.onKeyDown}
                                />
                            </td>
                            <td style={{textAlign: 'right'}}>
                                <button
                                    type='button'
                                    className={'btn btn-sm btn-danger'}
                                    onClick={() => this.props.fields.remove(index)}
                                >
                                    <span className='glyphicon glyphicon-remove'/>
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

}

export default EnumMetaArrayField;