<?php

namespace EN\IgdbApiBundle\Igdb\Parameter;


/**
 * The builder is used to form the query string which will be sent to the API.
 * It utilizes method chaining to gather the parameters' values and upon calling
 * the buildQueryString() method - they're combined into a query string.
 *
 * @author Emanuel Nikolov <enikolov.intl@gmail.com>
 */
class ParameterBuilder implements ParameterBuilderInterface
{

    /**
     * @var array
     */
    private $expand;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $ids = [];

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var string
     */
    private $order;

    /**
     * @var string
     */
    private $search;

    /**
     * @var string
     */
    private $scroll;

    /**
     * {@inheritdoc}
     */
    public function setExpand(string $expand): ParameterBuilderInterface
    {
        $this->expand[] = $expand;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(string $fields): ParameterBuilderInterface
    {
        $this->fields[] = $fields;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilters(
      string $field,
      $value,
      string $postfix = '='
    ): ParameterBuilderInterface {
        $this->filters[] = [
            'field' => $field,
            'postfix' => $postfix,
            'value' => $value,
        ];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(int $id): ParameterBuilderInterface
    {
        $this->ids[] = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setIds(string $ids): ParameterBuilderInterface
    {
        $this->ids[] = $ids;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLimit(int $limit): ParameterBuilderInterface
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOffset(int $offset): ParameterBuilderInterface
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(string $order): ParameterBuilderInterface
    {
        $this->order = $order;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearch(string $search): ParameterBuilderInterface
    {
        $this->search = $search;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setScroll(string $scroll): ParameterBuilderInterface
    {
        $this->scroll = $scroll;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryString(): string
    {
        // since the new api is in use, we dont need any get parameters
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $props = get_object_vars($this);

        foreach ($props as $key => $prop) {
            $this->$key = null;
        }
    }

    /**
     * Build the body part from the provided parameters
     *
     * @return string
     */
    public function buildBody(): string
    {
        $body = [];

        $propsArr = get_object_vars($this);

        // add default params
        foreach ($propsArr as $key => $prop) {
            if ($key === 'filters' || $key === 'search' || $key === 'ids') continue;

            // faster than is_array smh
            if ((array)$prop === $prop) {
                $body[$key] = implode(',', $prop);
            } elseif ($prop !== null) {
                $body[$key] = $prop;
            }
        }

        empty($body['fields']) ? $body['fields'] = '*' : null;

        // add search field
        if ($this->search) {
            $body['search'] = "\"{$this->search}\"";
        }

        $filters = [];

        if (isset($propsArr['filters'])) {
            foreach ($propsArr['filters'] as $data) {
                $value = (int) $data['value'] === $data['value'] ? $data['value'] : "\"{$data['value']}\"";
                $filters[] = "{$data['field']} {$data['postfix']} $value";
            }

            if (count($this->ids)) {
                $idArray = implode(',', $this->ids);
                $filters[] = "id = [$idArray]";
            }

            $body['where'] = implode(' & ', $filters);
        }

        $returnBody = [];
        foreach ($body as $key => $element) {
            $returnBody[] = $key . ' ' . $element . ';';
        }

        return implode("\n", $returnBody);
    }
}