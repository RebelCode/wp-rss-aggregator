<?php

namespace RebelCode\Wpra\Core\Data\Collections;

use ArrayIterator;
use Dhii\Output\TemplateInterface;
use Exception;
use LogicException;
use RebelCode\Wpra\Core\Data\AbstractDataSet;
use RebelCode\Wpra\Core\Templates\TwigTemplate;
use Twig\Environment;
use Twig\Error\LoaderError;

/**
 * A dataset implementation that acts as a collection for Twig templates.
 *
 * @since 4.13
 */
class TwigTemplateCollection extends AbstractDataSet
{
    /**
     * The Twig environment.
     *
     * @since 4.13
     *
     * @var Environment
     */
    protected $env;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param Environment $env  The Twig environment instance.
     */
    public function __construct(Environment $env)
    {
        $this->env = $env;
    }

    /**
     * Creates a template instance.
     *
     * @since 4.13
     *
     * @param string $path The path to the Twig file, relative from any registered templates directory.
     *
     * @return TemplateInterface The template instance.
     */
    protected function createTemplate($path)
    {
        return new TwigTemplate($this->env, $path);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function get($key)
    {
        return $this->createTemplate($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function has($key)
    {
        try {
            $this->env->load($key);
        } catch (LoaderError $e) {
            return false;
        } catch (Exception $e) {
            // Do not emit runtime or template syntax errors
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $value)
    {
        throw new LogicException(__('Cannot modify Twig templates', 'wprss'));
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function delete($key)
    {
        throw new LogicException(__('Cannot delete Twig templates', 'wprss'));
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getIterator()
    {
        return new ArrayIterator([]);
    }
}
