import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Field, reduxForm, formValueSelector} from 'redux-form';
import _upperFirst from 'lodash/upperFirst';

class CrudFrom extends React.Component {

    static formId = 'CrudFrom';

    static propTypes = {
        modules: PropTypes.arrayOf(PropTypes.shape({
            id: PropTypes.string,
            className: PropTypes.string,
        })),
        models: PropTypes.arrayOf(PropTypes.shape({
            id: PropTypes.string,
            name: PropTypes.string,
            module: PropTypes.string,
            className: PropTypes.string,
        })),
        formValues: PropTypes.object,
        csrfToken: PropTypes.string,
    };

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
                        Model
                    </label>
                    <div className='col-sm-10'>
                        <Field
                            name='modelClassName'
                            component='input'
                            list={`${CrudFrom.formId}_modelNameList`}
                            className='form-control'
                        />
                        <datalist id={`${CrudFrom.formId}_modelNameList`}>
                            {this.props.models.map(model => (
                                <option key={model.className} value={model.className} />
                            ))}
                        </datalist>
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Module
                    </label>
                    <div className='col-sm-10'>
                        <Field
                            name='moduleId'
                            component='input'
                            list={`${CrudFrom.formId}_moduleIdList`}
                            className='form-control'
                        />
                        <datalist id={`${CrudFrom.formId}_moduleIdList`}>
                            {this.props.modules.map(module => (
                                <option key={module.id} value={module.id} />
                            ))}
                        </datalist>
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Controller Name
                    </label>
                    <div className='col-sm-10 form-inline'>
                        <Field
                            name='name'
                            component='input'
                            className='form-control'
                        />
                        &nbsp;&nbsp;&nbsp;
                        {this.props.formValues.moduleId && this.props.formValues.name && (
                            <span className='text-muted'>
                                app\
                                {this.props.formValues.moduleId.replace(/\./g, '\\')}
                                \controller\
                                {_upperFirst(this.props.formValues.name)}
                                Controller
                            </span>
                        )}
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Url to index
                    </label>
                    <div className='col-sm-10'>
                        <Field
                            name='url'
                            component='input'
                            className='form-control'
                            placeholder='profile/orders'
                        />
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Access roles
                    </label>
                    <div className='col-sm-10'>
                        <Field
                            name='roles'
                            component='input'
                            className='form-control'
                            placeholder='*'
                            list={`${CrudFrom.formId}_rolesList`}
                        />
                        <datalist id={`${CrudFrom.formId}_rolesList`}>
                            <option value='@' />
                            <option value='admin' />
                        </datalist>
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Request fields (optional)
                    </label>
                    <div className='col-sm-10'>
                        <Field
                            name='requestFields'
                            component='input'
                            className='form-control'
                            placeholder='userId, orderId'
                        />
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Title
                    </label>
                    <div className='col-sm-10'>
                        <Field
                            name='title'
                            component='input'
                            className='form-control'
                            placeholder='Список пользователей'
                        />
                    </div>
                </div>
                <div className='form-group'>
                    <label className='col-sm-2 control-label'>
                        Actions
                    </label>
                    <div className='col-sm-10'>
                        <div className='checkbox'>
                            <label>
                                <Field
                                    name='createActionIndex'
                                    component='input'
                                    type='checkbox'
                                />
                                &nbsp;
                                Index
                            </label>
                        </div>
                        <div className='checkbox' style={{marginLeft: '15px'}}>
                            <label>
                                <Field
                                    name='withSearch'
                                    component='input'
                                    type='checkbox'
                                />
                                &nbsp;
                                with search
                            </label>
                        </div>
                        <div className='checkbox' style={{marginLeft: '15px'}}>
                            <label>
                                <Field
                                    name='withDelete'
                                    component='input'
                                    type='checkbox'
                                />
                                &nbsp;
                                with delete action
                            </label>
                        </div>
                        <div className='checkbox'>
                            <label>
                                <Field
                                    name='createActionCreate'
                                    component='input'
                                    type='checkbox'
                                />
                                &nbsp;
                                Create
                            </label>
                        </div>
                        <div className='checkbox'>
                            <label>
                                <Field
                                    name='createActionUpdate'
                                    component='input'
                                    type='checkbox'
                                />
                                &nbsp;
                                Update
                            </label>
                        </div>
                        <div className='checkbox'>
                            <label>
                                <Field
                                    name='createActionView'
                                    component='input'
                                    type='checkbox'
                                />
                                &nbsp;
                                View
                            </label>
                        </div>
                    </div>
                </div>
                <div className='form-group'>
                    <div className='col-sm-offset-2 col-sm-10'>
                        <button
                            type='submit'
                            className='btn btn-success'
                        >
                            {'Создать'}
                        </button>
                    </div>
                </div>
            </form>
        );
    }

}

const selector = formValueSelector(CrudFrom.formId);
export default __appWidget.register('\\extpoint\\yii2\\gii\\widgets\\CrudFrom\\CrudFrom', connect(
    state => ({
        formValues: {
            moduleId: selector(state, 'moduleId'),
            name: selector(state, 'name'),
        }
    })
)(reduxForm({
    form: CrudFrom.formId,
})(CrudFrom)));