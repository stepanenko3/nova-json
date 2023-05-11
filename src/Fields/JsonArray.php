<?php

namespace Stepanenko3\NovaJson\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class JsonArray extends Field
{
    use SupportsDependentFields;

    public Field $field;

    public $component = 'nova-json-array-field';


    public function field(Field $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function fill(NovaRequest $request, $model)
    {
        $model->{$this->attribute} = null;

        foreach (($request[$this->attribute] ?? []) as $index => $fieldset) {
            $this->field->fillAttribute(
                request: new NovaRequest($request[$this->attribute]),
                requestAttribute: $index,
                model: $model,
                attribute: "{$this->attribute}->{$index}",
            );
        }
    }

    public function getRules(NovaRequest $request)
    {
        return [
            $this->attribute => is_callable($this->rules) ? call_user_func($this->rules, $request) : $this->rules,
            $this->attribute . '.*' => is_callable($this->field->rules) ? call_user_func($this->field->rules, $request) : $this->field->rules,
        ];
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'field' => $this->field,
        ]);
    }
}
