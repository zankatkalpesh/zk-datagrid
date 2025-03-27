<?php

declare(strict_types=1);

namespace Zk\DataGrid;


class Action
{

    /**
     * Final output data.
     * 
     * @var array
     */
    protected ?array $output = null;

    /**
     * Create a action instance.
     */
    public function __construct(
        public int $index,
        public string $title,
        public mixed $icon = null,
        public mixed $method = null,
        public mixed $url = null,
        public mixed $formatter = null,
        public bool $escape = true,
        public array $attributes = [],
        public string $component = 'datagrid::action',
    ) {}

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
     * is callable url.
     * 
     * @return bool
     */
    public function isCallableUrl(): bool
    {
        return $this->url != null && is_callable($this->url);
    }

    /**
     * is formatter.
     * 
     * @return bool
     */
    public function isFormatter(): bool
    {
        return $this->formatter != null && is_callable($this->formatter);
    }

    /**
     * Get formatter.
     * 
     * @return mixed
     */
    public function getFormatter(): mixed
    {
        return $this->formatter;
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
     * Get attributes.
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get component.
     * 
     * @return string
     */
    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Transforms action to Array
     * 
     * @return array | null
     */
    public function toArray(): array
    {
        if ($this->output !== null) {
            return $this->output;
        }

        $this->output = [
            'index' => $this->getIndex(),
            'title' => $this->getTitle(),
            'icon' => $this->getIcon(),
            'formatIcon' => $this->isCallableIcon(),
            'method' => $this->getMethod(),
            'url' => (!$this->isCallableUrl()) ? $this->getUrl() : null,
            'formatter' => $this->isFormatter(),
            'escape' => $this->isEscape(),
            'attributes' => $this->getAttributes(),
            'component' => $this->getComponent(),
        ];

        return $this->output;
    }
}
