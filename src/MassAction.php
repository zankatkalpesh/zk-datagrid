<?php

declare(strict_types=1);

namespace Zk\DataGrid;

class MassAction
{

    use Traits\GeneralMethods;

    /**
     * Final output data.
     * 
     * @var array
     */
    protected ?array $output = null;

    /**
     * Attributes string.
     * 
     * @var string
     */
    protected string $attrStr;

    /**
     * Create a mass action instance.
     */
    public function __construct(
        public int $index,
        public string $title,
        public mixed $value = null,
        public mixed $icon = null,
        public mixed $method = null,
        public mixed $url = null,
        public bool $escape = true,
        public array $options = [],
        public array $params = [],
        public array $attributes = []
    ) {
        $this->attrStr = $this->printAttributes($this->attributes);

        // Options attributes string
        if (!empty($this->options)) {
            $this->options = array_map(function ($opt) {
                $opt['attributes'] = $opt['attributes'] ?? [];
                $opt['attributesString'] = $this->printAttributes($opt['attributes']);
                return $opt;
            }, $this->options);
        }
    }

    /**
     * Get index.
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Get title.
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get value.
     * 
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * is callable icon.
     * 
     * @return bool
     */
    public function isCallableIcon(): bool
    {
        return $this->icon != null && is_callable($this->icon);
    }

    /**
     * Get icon.
     * 
     * @return mixed
     */
    public function getIcon(): mixed
    {
        $icon = $this->icon;

        return ($this->isCallableIcon()) ? $icon() : $icon;
    }

    /**
     * Get method.
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return (in_array(strtoupper($this->method), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) ? strtoupper($this->method) : 'GET';
    }

    /**
     * Get url.
     * 
     * @return mixed
     */
    public function getUrl(): mixed
    {
        return $this->url;
    }

    /**
     * is escape.
     * 
     * @return bool
     */
    public function isEscape(): bool
    {
        return $this->escape;
    }

    /**
     * Get options.
     * 
     * @return mixed
     */
    public function getOptions(): mixed
    {
        return $this->options;
    }

    /**
     * Get params.
     * 
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get attributes.
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get attributes string.
     * 
     * @return string
     */
    public function getAttributesString(): string
    {
        return $this->attrStr;
    }

    /**
     * Transform the mass action to an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        if ($this->output !== null) {
            return $this->output;
        }

        $this->output = [
            'index' => $this->getIndex(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'icon' => $this->getIcon(),
            'method' => $this->getMethod(),
            'url' => $this->getUrl(),
            'escape' => $this->isEscape(),
            'options' => $this->getOptions(),
            'params' => $this->getParams(),
            'attributes' => $this->getAttributes(),
            'attributesString' => $this->getAttributesString(),
        ];

        return $this->output;
    }
}
