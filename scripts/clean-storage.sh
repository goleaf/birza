#!/bin/bash

# Clean storage directories (Unix/Linux/Mac)

# Remove log files
rm -f storage/logs/*.log

# Clean cache data directory (excluding .gitignore)
find storage/framework/cache/data/ -type f ! -name '.gitignore' -delete

# Clean debugbar directory (excluding .gitignore)
find storage/debugbar/ -type f ! -name '.gitignore' -delete

# Clean views directory (excluding .gitignore)
find storage/framework/views/ -type f ! -name '.gitignore' -delete

# Clean sessions directory (excluding .gitignore)
find storage/framework/sessions/ -type f ! -name '.gitignore' -delete

