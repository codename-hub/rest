<?php

namespace codename\rest\helper;

use codename\core\app;
use codename\core\exception;
use codename\core\model;
use codename\core\ui\crud;
use codename\core\ui\field;
use codename\rest\model\exposesRemoteApiInterface;
use ReflectionException;

/**
 * helper for context
 */
class context extends \codename\core\ui\helper\context
{

    public function __construct()
    {
    }

    /**
     * @param array $filterData
     * @param model $model
     * @param array $currentStructure
     * @param array $modelFields
     * @param array $modelFieldSettings
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public static function applicableModelFilter(array $filterData, model &$model, array $currentStructure, array $modelFields, array $modelFieldSettings = []): void
    {
        $filterData = array_filter($filterData, function ($v, $k) {
            if ($v === null || $v === '') {
                return false;
            }
            if (str_starts_with($k, '___')) {
                return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);

        if (count($filterData) === 0) {
            return;
        }

        $applicableFilter = self::getModelFilter($model, $currentStructure, $modelFields, $modelFieldSettings);

        foreach ($filterData as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // skip custom filter
            if (str_starts_with($key, '___')) {
                continue;
            }

            if (array_key_exists($key, $applicableFilter)) {
                // apply filter
                $applyFilter = $applicableFilter[$key];

                $diveModel = &$model;
                foreach ($applyFilter['structure'] as $modelName) {
                    if ($diveModel->getIdentifier() == $modelName) {
                        //
                    } else {
                        $nested = $diveModel->getNestedJoins($modelName);
                        if (count($nested) === 1) {
                            $diveModel = &$nested[0]->model;
                        } else {
                            // error!
                        }
                    }
                }

                if (!is_array($value)) {
                    // Fallback to '='
                    if (($applyFilter['operator'] ?? false) && $applyFilter['operator'] === 'LIKE') {
                        if (!str_ends_with($value, '%')) {
                            $value .= '%';
                        }
                    }
                    $diveModel->addDefaultFilter($applyFilter['field'], $value, $applyFilter['operator'] ?? '=');
                } else {
                    // Differentiate
                    $diveModel->addDefaultFilter($applyFilter['field'], $value['value'] ?? $value, $value['operator'] ?? '=');
                }
            }
        }

    }

    /**
     * [getModelFilter description]
     * @param model $model [description]
     * @param array $currentStructure [description]
     * @param array $modelFields [description]
     * @param array $modelFieldSettings [description]
     * @return array                                  [description]
     * @throws ReflectionException
     * @throws exception
     */
    public static function getModelFilter(model $model, array $currentStructure, array $modelFields, array $modelFieldSettings = []): array
    {
        $filters = [];

        // filter out arrays
        $fields = array_filter(array_values($modelFields), function ($item) {
            return !is_array($item);
        });

        // check custom filter
        foreach ($fields as $field) {
            if (!str_starts_with($field, '___')) {
                continue;
            }

            // custom filters
            $id = [$field];
            $idString = implode('.', $id);
            $fieldName = app::getTranslate()->translate('DATAFIELD.' . substr($field, 3));

            $fieldConfig = [
              'field_ajax' => false,
              'field_class' => 'input',
              'field_datatype' => 'text',
              'field_description' => '',
              'field_fieldtype' => 'input',
              'field_id' => $field,
              'field_multiple' => false,
              'field_name' => $field,
              'field_noninput' => false,
              'field_placeholder' => $fieldName,
              'field_readonly' => false,
              'field_required' => false,
              'field_title' => $fieldName,
              'field_type' => 'input',
              'field_validator' => '',
              'field_value' => null,
            ];

            $filters[$field] = array_merge(
              [
                'structure' => [],
                'filter_identifier' => $id,
                'filter_name' => $idString,
                'model' => $model->getIdentifier(),
                'label' => $fieldName,
                'field' => $field,
                'operator' => null,
                'datatype' => $fieldConfig['field_datatype'],
              ],
              $modelFieldSettings[$idString] ?? []
            );
            $filters[$field]['field_config'] = array_merge($fieldConfig, $modelFieldSettings[$idString]['field_config'] ?? []);
        }

        // get model field filter
        foreach ($model->getFields() as $field) {
            if (in_array($field, $fields)) {
                // determine a type and stuff

                $id = array_merge($currentStructure, [$field]);
                $idString = implode('.', $id);

                $datatype = $model->getConfig()->get('datatype>' . $field);
                if ($datatype === 'text_timestamp' || $datatype === 'text_date') {
                    $filters[$idString . '.from'] = array_merge(
                      [
                        'structure' => $currentStructure,
                        'filter_identifier' => array_merge($id, ['from']),
                        'filter_name' => $idString . '.from',
                        'model' => $model->getIdentifier(),
                          // TODO: add translation to identify a field in a nested model as we can have multiple occurrences?
                          // e.g. the 'Customer Person's Lastname'?
                        'label' => app::getTranslate()->translate('DATAFIELD.' . $field . '__from'),
                        'field' => $field,
                        'operator' => '>=',
                        'datatype' => $model->getConfig()->get('datatype>' . $field),
                      ],
                      $modelFieldSettings[$idString] ?? []
                    );
                    $filters[$idString . '.from']['field_config'] = self::makeField(
                      $model,
                      $field,
                      array_merge(
                        [
                          'field_title' => app::getTranslate()->translate('DATAFIELD.' . $field . '__from'),
                        ],
                        $modelFieldSettings[$idString]['field_config'] ?? [],
                      )
                    );
                    $filters[$idString . '.until'] = array_merge(
                      [
                        'structure' => $currentStructure,
                        'filter_identifier' => array_merge($id, ['until']),
                        'filter_name' => $idString . '.until',
                        'model' => $model->getIdentifier(),
                          // TODO: add translation to identify a field in a nested model as we can have multiple occurrences?
                          // e.g. the 'Customer Person's Lastname'?
                        'label' => app::getTranslate()->translate('DATAFIELD.' . $field . '__until'),
                        'field' => $field,
                        'operator' => '<=',
                        'datatype' => $model->getConfig()->get('datatype>' . $field),
                      ],
                      $modelFieldSettings[$idString] ?? []
                    );
                    $filters[$idString . '.until']['field_config'] = self::makeField(
                      $model,
                      $field,
                      array_merge(
                        [
                          'field_title' => app::getTranslate()->translate('DATAFIELD.' . $field . '__until'),
                        ],
                        $modelFieldSettings[$idString]['field_config'] ?? [],
                      )
                    );
                } else {
                    $filters[$idString] = array_merge(
                      [
                        'structure' => $currentStructure,
                        'filter_identifier' => $id,
                        'filter_name' => $idString,
                        'model' => $model->getIdentifier(),
                          // TODO: add translation to identify a field in a nested model as we can have multiple occurrences?
                          // e.g. the 'Customer Person's Lastname'?
                        'label' => app::getTranslate()->translate('DATAFIELD.' . $field),
                        'field' => $field,
                        'operator' => null,
                        'datatype' => $model->getConfig()->get('datatype>' . $field),
                      ],
                      $modelFieldSettings[$idString] ?? []
                    );
                    $filters[$idString]['field_config'] = self::makeField($model, $field, $modelFieldSettings[$idString]['field_config'] ?? []);
                }
            }
        }

        foreach ($modelFields as $key => $value) {
            if (is_array($value)) {
                // determine model from nested models
                $nested = $model->getNestedJoins($key);
                foreach ($nested as $join) {
                    $addFilters = self::getModelFilter($join->model, array_merge($currentStructure, [$key]), $value, $modelFieldSettings);
                    $filters = array_merge($filters, $addFilters);
                }
            }
        }

        return $filters;
    }


    /**
     * Creates the field instance for the given field and adds information to it.
     *
     * @param model $model [description]
     * @param string $field [description]
     * @param array $options [description]
     * @return field          [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected static function makeField(model $model, string $field, array $options = []): field
    {
        // load model config for simplicity
        $modelconfig = $model->config->get();

        // Error if field not in models
        if (!in_array($field, $model->getFields())) {
            throw new exception('EXCEPTION_MAKEFIELD_FIELDNOTFOUNDINMODEL', exception::$ERRORLEVEL_ERROR, $field);
        }

        // Create a basic formfield array
        $fielddata = [
          'field_id' => $field,
          'field_name' => $field,
          'field_title' => app::getTranslate()->translate('DATAFIELD.' . $field),
          'field_description' => app::getTranslate()->translate('DATAFIELD.' . $field . '_DESCRIPTION'),
          'field_type' => 'input',
          'field_required' => false,
          'field_placeholder' => app::getTranslate()->translate('DATAFIELD.' . $field),
          'field_multiple' => false,
          'field_readonly' => $options['field_readonly'] ?? false,
        ];

        // Get the displaytype of this field
        if (array_key_exists('datatype', $modelconfig) && array_key_exists($field, $modelconfig['datatype'])) {
            $fielddata['field_type'] = crud::getDisplaytypeStatic($modelconfig['datatype'][$field]);
            $fielddata['field_datatype'] = $modelconfig['datatype'][$field];
        }

        if ($fielddata['field_type'] == 'yesno') {
            $fielddata['field_type'] = 'select';
            $fielddata['field_displayfield'] = '{$element[\'field_name\']}';
            $fielddata['field_valuefield'] = 'field_value';

            // NOTE: Datatype for this kind of pseudo-boolean field must be null or so
            // because the boolean validator really needs a bool.
            $fielddata['field_datatype'] = null;
            $fielddata['field_elements'] = [
              [
                'field_value' => true,
                'field_name' => 'Ja',
              ],
              [
                'field_value' => false,
                'field_name' => 'Nein',
              ],
            ];
        }

        // Modify field to be a reference dropdown
        if (array_key_exists('foreign', $modelconfig) && array_key_exists($field, $modelconfig['foreign'])) {
            if (!app::getValidator('structure_config_modelreference')->isValid($modelconfig['foreign'][$field])) {
                throw new exception('EXCEPTION_MAKEFIELD_INVALIDREFERENCEOBJECT', exception::$ERRORLEVEL_ERROR, $modelconfig['foreign'][$field]);
            }

            $foreign = $modelconfig['foreign'][$field];

            $elements = app::getModel($foreign['model'], $foreign['app'] ?? app::getApp());

            if (array_key_exists('order', $foreign) && is_array($foreign['order'])) {
                foreach ($foreign['order'] as $order) {
                    if (!app::getValidator('structure_config_modelorder')->isValid($order)) {
                        throw new exception('EXCEPTION_MAKEFIELD_INVALIDORDEROBJECT', exception::$ERRORLEVEL_ERROR, $order);
                    }
                    $elements->addOrder($order['field'], $order['direction']);
                }
            }

            if (array_key_exists('filter', $foreign) && is_array($foreign['filter'])) {
                foreach ($foreign['filter'] as $filter) {
                    if (!app::getValidator('structure_config_modelfilter')->isValid($filter)) {
                        throw new exception('EXCEPTION_MAKEFIELD_INVALIDFILTEROBJECT', exception::$ERRORLEVEL_ERROR, $filter);
                    }
                    if ($filter['field'] == $elements->getIdentifier() . '_flag') {
                        if ($filter['operator'] == '=') {
                            $elements->withFlag($elements->config->get('flag>' . $filter['value']));
                        } elseif ($filter['operator'] == '!=') {
                            $elements->withoutFlag($elements->config->get('flag>' . $filter['value']));
                        } else {
                            throw new exception('EXCEPTION_MAKEFIELD_FILTER_FLAG_INVALIDOPERATOR', exception::$ERRORLEVEL_ERROR, $filter);
                        }
                    } else {
                        $elements->addFilter($filter['field'], $filter['value'], $filter['operator']);
                    }
                }
            }

            $fielddata['field_type'] = 'select';
            $fielddata['field_displayfield'] = $foreign['display'];
            $fielddata['field_valuefield'] = $foreign['key'];

            if ($elements instanceof exposesRemoteApiInterface && isset($foreign['remote_source'])) {
                $apiEndpoint = $elements->getExposedApiEndpoint();
                $fielddata['field_remote_source'] = $apiEndpoint;

                $remoteSource = $foreign['remote_source'];

                $filterKeys = [];
                foreach ($remoteSource['filter_key'] as $filterKey => $filterData) {
                    if (is_array($filterData)) {
                        foreach ($filterData as $filterDataData) {
                            $filterKeys[$filterKey][$filterDataData] = true;
                        }
                    } else {
                        $filterKeys[$filterData] = true;
                    }
                }

                $fielddata['field_remote_source_filter_key'] = $filterKeys;
                $fielddata['field_remote_source_parameter'] = $remoteSource['parameters'] ?? [];
                $fielddata['field_remote_source_display_key'] = $remoteSource['display_key'] ?? null;
                $fielddata['field_remote_source_links'] = $foreign['remote_source']['links'] ?? [];
                $fielddata['field_valuefield'] = $foreign['key'];
                $fielddata['field_displayfield'] = $foreign['key'];
            } else {
                $fielddata['field_elements'] = $elements->search()->getResult();
            }

            if (array_key_exists('datatype', $modelconfig) && array_key_exists($field, $modelconfig['datatype']) && $modelconfig['datatype'][$field] == 'structure') {
                $fielddata['field_multiple'] = true;
            }
        }

        $fielddata = array_replace($fielddata, $options);

        $field = new field($fielddata);
        $field->setType('compact');

        // Add the field to the form
        return $field;
    }

}
