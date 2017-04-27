import React from 'react';
import PropTypes from 'prop-types';
import {arrayPush} from 'redux-form/lib/actions';

import {html} from 'components';
import ModelMetaRow from './ModelMetaRow';

import './ModelMetaArrayField.less';
const bem = html.bem('ModelMetaArrayField');

class ModelMetaArrayField extends React.Component {

    static formId = 'ModelEditor';

    static propTypes = {
        fields: PropTypes.object,
        appTypes: PropTypes.arrayOf(PropTypes.shape({
            name: PropTypes.string
        })),
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
                        <ModelMetaRow
                            key={index}
                            attribute={attribute}
                            index={index}
                            appTypes={this.props.appTypes}
                            onKeyDown={this.props.onKeyDown}
                            onRemove={() => this.props.fields.remove(index)}
                        >
                        </ModelMetaRow>
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

export default ModelMetaArrayField;