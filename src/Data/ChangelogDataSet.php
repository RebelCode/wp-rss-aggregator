<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;
use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;
use RuntimeException;

/**
 * A data set for changelog files that adhere to the {@link http://keepachangelog.com Keep a Changelog} standard.
 *
 * @since [*next-version*]
 */
class ChangelogDataSet implements DataSetInterface
{
    /* @since [*next-version*] */
    use IteratorDelegateTrait;

    /**
     * The path to the changelog file.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $file;

    /**
     * The parsed changelog data.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $parsed;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $file The path to the changelog file.
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->parsed = null;
    }

    /**
     * Checks if the changelog needs to be parsed and if so, it is parsed.
     *
     * @since [*next-version*]
     */
    protected function init()
    {
        if ($this->parsed === null) {
            $this->parse();
        }
    }

    /**
     * Parses the changelog file.
     *
     * @since [*next-version*]
     */
    protected function parse()
    {
        $handle = fopen($this->file, 'r');

        $this->parsed = [];
        $currHeading = 0;
        $version = '';

        do {
            $buffer = fgets($handle);
            if ($buffer === false) {
                break;
            }

            // If an empty line
            if (strlen(trim($buffer)) === 0) {
                $version = '';
                continue;
            }

            // If a level-2 heading
            if (strpos($buffer, '## ', 0) === 0) {
                $currHeading = 2;

                preg_match('/##\s\[([^\]]+)\]\s+-\s+(.*)/', $buffer, $matches);
                if (isset($matches[1])) {
                    $version = trim($matches[1]);
                    $date = isset($matches[2]) ? trim($matches[2]) : '';

                    $this->parsed[$version] = [
                        'date' => $date,
                        'lines' => [],
                        'raw' => '',
                    ];
                }

                continue;
            }

            if ($currHeading === 2) {
                $this->parsed[$version]['lines'][] = $buffer;
                $this->parsed[$version]['raw'] .= $buffer;
            }
        } while (true);

        fclose($handle);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetGet($offset)
    {
        $this->init();

        if (isset($this->parsed[$offset])) {
            return $this->parsed[$offset];
        }

        throw new RuntimeException(sprintf(__('No changelog entry found for version %s', 'wprss'), $offset));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetExists($offset)
    {
        $this->init();

        return isset($this->parsed[$offset]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetSet($offset, $value)
    {
        $this->init();

        $this->parsed[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetUnset($offset)
    {
        $this->init();

        unset($this->parsed[$offset]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        $this->init();

        return new ArrayIterator($this->parsed);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __toString()
    {
        $this->init();

        $string = '';
        foreach ($this as $version => $info) {
            $string .= sprintf('## %s (%s)', $version, $info['date']) . PHP_EOL . $info['raw'] . PHP_EOL;
        }

        return $string;
    }
}
