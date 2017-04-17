import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Field, FieldArray, reduxForm, formValueSelector} from 'redux-form';
import {initialize, arrayPush} from 'redux-form/lib/actions';

import ModelMetaArrayField from './views/ModelMetaArrayField';
import ModelRelationsArrayField from './views/ModelRelationsArrayField';

class ModelEditor extends React.Component {

    static formId = 'ModelEditor';

    static propTypes = {
        modules: PropTypes.arrayOf(PropTypes.shape({
            id: PropTypes.string,
            className: PropTypes.string,
        })),
        models: PropTypes.arrayOf(PropTypes.shape({
            className: PropTypes.string,
            name: PropTypes.string,
            moduleClass: PropTypes.shape({
                id: PropTypes.string,
                className: PropTypes.string,
            }),
            metaClass: PropTypes.shape({
                className: PropTypes.string,
                meta: PropTypes.array,
                relations: PropTypes.array,
            }),
        })),
        tableNames: PropTypes.array,
        appTypes: PropTypes.array,
        formValues: PropTypes.object,
        csrfToken: PropTypes.string,
    };

    static defaultMeta = [
        {
            name: 'id',
            label: 'ID',
            appType: 'primaryKey',
        },
    ];

    constructor() {
        super(...arguments);

        this._onTableKeyDown = this._onTableKeyDown.bind(this);
    }

    componentWillReceiveProps(nextProps) {
        if ((!this.props.formValues.moduleId || !this.props.formValues.modelName)
            && nextProps.formValues.moduleId && nextProps.formValues.modelName) {

            const model = this.getModel(nextProps);
            const values = {
                ...nextProps.formValues,
                meta: model ? model.metaClass.meta : ModelEditor.defaultMeta,
                relations: model ? model.metaClass.relations : [],
            };
            if (model) {
                values.tableName = model.tableName;
            }

            this.props.dispatch(initialize(ModelEditor.formId, values));
        }
    }

    getModel(props) {
        props = props || this.props;
        return props.models.find(m => m.moduleClass.id === props.formValues.moduleId && m.name === props.formValues.modelName);
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
                            list={`${ModelEditor.formId}_moduleIdList`}
                            className='form-control'
                        />
                        <datalist id={`${ModelEditor.formId}_moduleIdList`}>
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
                            name='modelName'
                            component='input'
                            list={`${ModelEditor.formId}_modelNameList`}
                            className='form-control'
                        />
                        <datalist id={`${ModelEditor.formId}_modelNameList`}>
                            {this.props.models.filter(model => model.moduleClass.id === this.props.formValues.moduleId).map(model => (
                                <option key={model.name} value={model.name} />
                            ))}
                        </datalist>
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Table name
                    </label>
                    <div className='col-sm-8'>
                        <Field
                            name='tableName'
                            component='input'
                            list={`${ModelEditor.formId}_tableNameList`}
                            className='form-control'
                        />
                        <datalist id={`${ModelEditor.formId}_tableNameList`}>
                            {this.props.tableNames.map(name => (
                                <option key={name} value={name} />
                            ))}
                        </datalist>
                    </div>
                </div>
                {this.props.formValues.moduleId && this.props.formValues.modelName && (
                    <FieldArray
                        name='meta'
                        component={ModelMetaArrayField}
                        appTypes={this.props.appTypes}
                        onKeyDown={e => this._onTableKeyDown(e, 'meta')}
                    />
                )}
                {this.props.formValues.moduleId && this.props.formValues.modelName && (
                    <FieldArray
                        name='relations'
                        component={ModelRelationsArrayField}
                        models={this.props.models}
                        onKeyDown={e => this._onTableKeyDown(e, 'relations')}
                    />
                )}
                <div>
                    <h3>
                        Migrations
                    </h3>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Migration mode
                    </label>
                    <div className='col-sm-8'>
                        <Field
                            name='migrateMode'
                            component='select'
                            className='form-control'
                        >
                            <option value='update'>Update (diff)</option>
                            <option value='create'>Create table</option>
                            <option value='none'>No migrate</option>
                        </Field>
                    </div>
                </div>
                <div className='form-group'>
                    <div className='col-sm-offset-2 col-sm-6'>
                        <button
                            type='submit'
                            className='btn btn-success'
                        >
                            {this.getModel() ? 'Обновить модель' : 'Создать модель'}
                        </button>
                    </div>
                </div>
            </form>
        );
    }

    _onTableKeyDown(e, name) {
        if (!e.shiftKey || [38, 40].indexOf(e.keyCode) === -1) {
            return;
        }

        e.preventDefault();

        let td = e.target;
        while ((td = td.parentElement) && td.tagName.toLowerCase() !== 'td') {} // eslint-disable-line no-empty

        const tr = td.parentNode;
        const trs = Array.prototype.slice.call(tr.parentNode.childNodes);
        const columnIndex = Array.prototype.slice.call(tr.childNodes).indexOf(td);
        const rowIndex =  trs.indexOf(tr);
        const nextRowIndex = e.keyCode === 38 ? rowIndex - 1 : rowIndex + 1;

        if (nextRowIndex >= 0 && nextRowIndex < trs.length) {
            trs[nextRowIndex].childNodes[columnIndex].querySelector('input, select').focus();
        } else if (nextRowIndex === trs.length) {
            this.props.dispatch(arrayPush(ModelMetaArrayField.formId, name));
            setTimeout(() => {
                tr.parentNode.childNodes[nextRowIndex].childNodes[columnIndex].querySelector('input').focus();
            });
        }
    }

}

const selector = formValueSelector(ModelEditor.formId);
export default __appWidget.register('\\extpoint\\yii2\\gii\\widgets\\ModelEditor\\ModelEditor', connect(
    state => ({
        formValues: {
            moduleId: selector(state, 'moduleId'),
            modelName: selector(state, 'modelName'),
        }
    })
)(reduxForm({
    form: ModelEditor.formId,
})(ModelEditor)));