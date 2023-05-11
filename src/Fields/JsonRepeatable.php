<?php

namespace Stepanenko3\NovaJson\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Util;

class JsonRepeatable extends Field
{
    use SupportsDependentFields;

    public $fields;

    public $component = 'nova-json-repeatable-field';

    public function fields(array $fields): self
    {
        $this->fields = new FieldCollection($fields);

        return $this;
    }

    protected function resolveAttribute($resource, $attribute)
    {
        return Util::value(data_get($resource, str_replace('->', '.', $attribute)));
    }

    public function fill(NovaRequest $request, $model)
    {
        $model->{$this->attribute} = null;

        foreach(($request[$this->attribute] ?? []) as $index => $fieldset) {
            foreach($this->fields as $field) {
                $field->fillAttribute(
                    request: new NovaRequest($request[$this->attribute][$index]),
                    requestAttribute: $field->attribute,
                    model: $model,
                    attribute: "{$this->attribute}->{$index}->{$field->attribute}",
                );
            }
        }
    }

    public function getRules(NovaRequest $request)
    {
        $rules = [
            $this->attribute => is_callable($this->rules) ? call_user_func($this->rules, $request) : $this->rules,
        ];

        foreach($this->fields as $field) {
            $rules["{$this->attribute}.*.{$field->attribute}"] = is_callable($field->rules) ? call_user_func($field->rules, $request) : $field->rules;
        }

        return $rules;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'fields' => $this->fields,
        ]);
    }
}
