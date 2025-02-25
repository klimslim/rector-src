<?php

namespace Rector\Core\Tests\Issues\Issue6708\Fixture;

class Fixture2
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run($operation)
    {
        switch ($operation) {
            case 'fruits':
                return ['banana', 'orange', ...$this->getArray()];
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6708\Fixture;

class Fixture2
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run($operation)
    {
        switch ($operation) {
            case 'fruits':
                return array_merge(['banana', 'orange'], $this->getArray());
        }
    }
}

?>
