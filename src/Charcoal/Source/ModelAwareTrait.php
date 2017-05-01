<?php
namespace Charcoal\Source;

use Exception;

use Charcoal\Model\ModelInterface;

trait ModelAwareTrait
{
    /**
     * @var ModelInterface $model
     */
    private $model = null;

    /**
     * Set the source's Model.
     *
     * @param ModelInterface $model The source's model.
     * @return AbstractSource Chainable
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Return the source's Model.
     *
     * @throws Exception If not model was previously set.
     * @return ModelInterface
     */
    public function model()
    {
        if ($this->model === null) {
            throw new Exception(
                'No model set.'
            );
        }
        return $this->model;
    }

    /**
     * @return boolean
     */
    public function hasModel()
    {
        return ($this->model !== null);
    }
}
