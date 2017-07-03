import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Field, FieldArray, reduxForm, formValueSelector} from 'redux-form';
import {initialize, arrayPush} from 'redux-form/lib/actions';
import _isArray from 'lodash/isArray';

import tableNavigationHandler from '../ModelEditor/tableNavigationHandler';
import ModelMetaArrayField from '../ModelEditor/views/ModelMetaArrayField';

class FormModelEditor extends React.Component {

    static formId = 'ModelEditor';

    static propTypes = {
        modules: PropTypes.arrayOf(PropTypes.shape({
            id: PropTypes.string,
            className: PropTypes.string,
        })),
        models: PropTypes.arrayOf(PropTypes.shape({
            className: PropTypes.string,
            name: PropTypes.string,
        })),
        formModels: PropTypes.arrayOf(PropTypes.shape({
            className: PropTypes.string,
            name: PropTypes.string,
            moduleClass: PropTypes.shape({
                id: PropTypes.string,
                className: PropTypes.string,
            }),
            metaClass: PropTypes.shape({
                className: PropTypes.string,
                meta: PropTypes.array,
            }),
        })),
        appTypes: PropTypes.array,
        formValues: PropTypes.object,
        csrfToken: PropTypes.string,
    };

    constructor() {
        super(...arguments);

        this._onTableKeyDown = this._onTableKeyDown.bind(this);
    }

    componentWillReceiveProps(nextProps) {
        if ((!this.props.formValues.moduleId || !this.props.formValues.formModelName)
            && nextProps.formValues.moduleId && nextProps.formValues.formModelName) {

            const model = this.getModel(nextProps);
            const values = {
                ...nextProps.formValues,
                meta: model ? model.metaClass.meta : [],
            };
            if (model) {
                values.tableName = model.tableName;
            }

            this.props.dispatch(initialize(FormModelEditor.formId, values));
        }
    }

    getModel(props) {
        props = props || this.props;
        return props.formModels.find(m => m.moduleClass.id === props.formValues.moduleId && m.name === props.formValues.formModelName);
    }

    render() {
        return (
            <form
                method='post'
                className='form-horizontal'
            >
                <input
                    type='hidden'
                    name='_csrf'
                    value={this.props.csrfToken}
                />
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Module
                    </label>
                    <div className='col-sm-8'>
                        <Field
                            name='moduleId'
                            component='input'
                            list={`${FormModelEditor.formId}_moduleIdList`}
                            className='form-control'
                        />
                        <datalist id={`${FormModelEditor.formId}_moduleIdList`}>
                            {this.props.modules.map(module => (
                                <option key={module.id} value={module.id} />
                            ))}
                        </datalist>
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Model
                    </label>
                    <div className='col-sm-8'>
                        <Field
                            name='formModelName'
                            component='input'
                            list={`${FormModelEditor.formId}_modelNameList`}
                            className='form-control'
                        />
                        <datalist id={`${FormModelEditor.formId}_modelNameList`}>
                            {this.props.formModels.filter(model => model.moduleClass.id === this.props.formValues.moduleId).map(model => (
                                <option key={model.name} value={model.name} />
                            ))}
                        </datalist>
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        AR Model for ::find()
                    </label>
                    <div className='col-sm-8'>
                        <Field
                            name='modelClass'
                            component='input'
                            list={`${FormModelEditor.formId}_allModelNameList`}
                            className='form-control'
                        />
                        <datalist id={`${FormModelEditor.formId}_allModelNameList`}>
                            {this.props.models.map(model => (
                                <option key={model.className} value={model.className} />
                            ))}
                        </datalist>
                    </div>
                </div>
                <datalist id={`${FormModelEditor.formId}_selfAttributesList`}>
                    {this._getAttributes().map(name => (
                        <option key={name} value={name} />
                    ))}
                </datalist>
                {this._renderDataLists()}
                {this.props.formValues.moduleId && this.props.formValues.formModelName && (
                    <FieldArray
                        name='meta'
                        component={ModelMetaArrayField}
                        appTypes={this.props.appTypes}
                        onKeyDown={e => this._onTableKeyDown(e, 'meta')}
                    />
                )}
                <div className='form-group'>
                    <div className='col-sm-offset-2 col-sm-6'>
                        <button
                            type='submit'
                            className='btn btn-success'
                        >
                            {this.getModel() ? 'Обновить' : 'Создать'}
                        </button>
                    </div>
                </div>
            </form>
        );
    }

    _renderDataLists() {
        const lists = {};
        this.props.appTypes.forEach(appType => {
            Object.keys(appType.fieldProps).map(key => {
                const list = appType.fieldProps[key].list;
                switch (list) {
                    case 'types':
                        lists[key] = this.props.appTypes.map(r => r && r.name).filter(Boolean);
                        break;

                    case 'attributes':
                        lists[key] = this._getAttributes();
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

    _getAttributes() {
        const names = [];
        this.props.formValues.meta.forEach(item => {
            if (item && item.name) {
                names.push(item.name);

                (item.items || []).forEach(subItem => {
                    if (subItem && subItem.name) {
                        names.push(subItem.name);
                    }
                });
            }
        });
        return names;
    }

    _onTableKeyDown(e, name) {
        tableNavigationHandler(e, () => this.props.dispatch(arrayPush(ModelMetaArrayField.formId, name)));
    }

}

const selector = formValueSelector(FormModelEditor.formId);
export default __appWidget.register('\\extpoint\\yii2\\gii\\widgets\\FormModelEditor\\FormModelEditor', connect(
    state => ({
        formValues: {
            meta: selector(state, 'meta') || [],
            moduleId: selector(state, 'moduleId'),
            formModelName: selector(state, 'formModelName'),
        }
    })
)(reduxForm({
    form: FormModelEditor.formId,
})(FormModelEditor)));