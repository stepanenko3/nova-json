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

    protected function resolveAttribute($resource, $attribute): mixed
    {
        return Util::value(data_get($resource, str_replace('->', '.', $attribute)));
    }

    public function fill(NovaRequest $request, $model, string $nestedAttribute = null)
    {
        $this->fillModelWithData($model, null, $nestedAttribute ?? $this->attribute);

        foreach(($request[$this->attribute] ?? []) as $index => $fieldset) {
            foreach($this->fields as $field) {
                /** @var Field $field */

                $attributeKeys = [
                    $index,
                    $field->attribute,
                ];

                array_unshift($attributeKeys, $nestedAttribute ?? $this->attribute);

                $attribute = implode('->', $attributeKeys);

                if ($field instanceof self) {
                    $field->fill(
                        request: new NovaRequest($request[$this->attribute][$index]),
                        model: $model,
                        nestedAttribute: $attribute
                    );

                    continue;
                }

                $field->fillAttribute(
                    request: new NovaRequest($request[$this->attribute][$index]),
                    requestAttribute: $field->attribute,
                    model: $model,
                    attribute: $attribute,
                );
            }
        }
    }

    public function getRules(NovaRequest $request, string $nestedAttribute = null): array
    {
        $rules = [
            $nestedAttribute ?? $this->attribute => is_callable($this->rules) ? call_user_func($this->rules, $request) : $this->rules,
        ];

        foreach($this->fields as $field) {
            $key = $nestedAttribute ?? $this->attribute;

            if ($field instanceof self) {
                $rules = array_merge($rules, $field->getRules($request, "{$key}.*.{$field->attribute}"));

                continue;
            }

            $rules["{$key}.*.{$field->attribute}"] = $field->getRules($request)[$field->attribute];
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
