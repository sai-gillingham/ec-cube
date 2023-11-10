#bin/bash

for level in {0..3}
do
    docker exec ec-cube-ec-cube-1 bash -c "vendor/bin/phpstan analyse src --level ${level} > tests/results/level_${level}.json"
done

php tests/results/convert.php
