<?php

namespace codename\rest\context;

use codename\core\context;
use codename\core\exception;
use codename\core\model;
use codename\rest\response\json;
use LogicException;
use ReflectionException;

/**
 * Defines an HTTP Endpoint based on the REST Specification
 */
abstract class restcrud extends context implements restContextInterface
{
    /**
     * [EXCEPTION_REST_METHOD_NO_ID_PROVIDED description]
     * @var string
     */
    public const EXCEPTION_REST_METHOD_NO_ID_PROVIDED = 'EXCEPTION_REST_METHOD_NO_ID_PROVIDED';
    /**
     * Overwrite what model to use in the CRUD generator
     * @var null|string
     */
    protected ?string $modelName = null;
    /**
     * Overwrite the name of the app the requested model is located
     * @var null|string
     */
    protected ?string $modelApp = null;
    /**
     * Holds the model for this CRUD generator
     * @var null|model
     */
    protected ?model $model = null;

    /**
     * instantiate a new instance of this restcrud
     * @throws exception
     */
    public function __construct()
    {
        // reset response data
        // this is a data-only context
        if ($this->getResponse() instanceof json) {
            $this->getResponse()->reset();
        }
    }

    /**
     * default view.
     * @return void
     */
    public function view_default(): void
    {
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function method_get(): void
    {
        if ($this->getRequest()->isDefined('id')) {
            // SINGLE ENTRY

            $data = $this->getModelInstance()->entryLoad($this->getRequest()->getData('id'))->getData();
            // set output data
        } else {
            // List - no PKEY defined

            $model = $this->getModelInstance();

            // apply filters requested
            if ($this->getRequest()->isDefined('filter')) {
                foreach ($this->getRequest()->getData('filter') as $filter) {
                    $model->addFilter($filter['field'], $filter['value'], $filter['operator'] ?? '=');
                }
            }

            $data = $model->search()->getResult();
            // reset and set
        }
        $this->getResponse()->addData($data);
    }

    /**
     * implement this function and return your model
     * @return model
     */
    abstract public function getModelInstance(): model;

    /**
     * {@inheritDoc}
     */
    public function method_head(): void
    {
        // META
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function method_post(): void
    {
        // CREATE / custom stuff?
        $model = $this->getModelInstance();

        $model->entryMake($this->getRequest()->getData());
        if (count($errors = $model->entryValidate()) === 0) {
            $model->entrySave();

            if ($this->getRequest()->getData($model->getPrimaryKey())) {
                // id submitted, this was an update
                $this->getResponse()->setData('id', $this->getRequest()->getData($model->getPrimaryKey()));
            } else {
                // new created, return last insert id
                $this->getResponse()->setData('id', $model->lastInsertId());
            }
        } else {
            throw new exception('EXCEPTION_RESTCRUD_VALIDATION_ERROR', exception::$ERRORLEVEL_ERROR, $errors);
        }
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function method_put(): void
    {
        if ($this->getRequest()->isDefined('id')) {
            // update existing entry

            $model = $this->getModelInstance();

            $model->entryLoad($this->getRequest()->getData('id'));
            $model->entryUpdate($this->getRequest()->getData());
        } else {
            // create a new entry
            // may contain a primary key value, though
            $model = $this->getModelInstance();

            $model->entryMake($this->getRequest()->getData());
        }
        $model->entrySave();
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function method_delete(): void
    {
        if ($this->getRequest()->isDefined('id')) {
            $model = $this->getModelInstance();
            $model->entryLoad($this->getRequest()->getData('id'));
            $model->entryDelete();
        } else {
            // error: no id provided
            throw new exception(self::EXCEPTION_REST_METHOD_NO_ID_PROVIDED, exception::$ERRORLEVEL_ERROR);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function method_trace(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function method_options(): void
    {
        // show methods available
        // throw new \LogicException('Not implemented'); // TODO
        // we may change OPTIONS through header setting via $this->getResponse()->setHeader...
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function method_patch(): void
    {
        // EDIT
        if ($this->getRequest()->isDefined('id')) {
            $model = $this->getModelInstance();
            $model->entryLoad($this->getRequest()->getData('id'));
            $model->entryUpdate($this->getRequest()->getData());
            $model->entrySave();
        } else {
            // error: no id provided
            throw new exception(self::EXCEPTION_REST_METHOD_NO_ID_PROVIDED, exception::$ERRORLEVEL_ERROR);
        }
    }
}
