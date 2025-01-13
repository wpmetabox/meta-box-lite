#!/bin/bash

BASE_URL="https://translate.wordpress.org/projects/wp-plugins/meta-box/"
OUTPUT_DIR="languages/meta-box"

mkdir -p $OUTPUT_DIR

LANGUAGE_LINKS=$(curl -s $BASE_URL | sed -n 's/.*\(\/locale\/[^"]*\/default\/wp-plugins\/meta-box\/\).*/\1/p')

for LINK in $LANGUAGE_LINKS; do
    LANGUAGE_NAME=$(echo $LINK | sed -E 's/.*locale\/([^\/]+)\/default.*/\1/')

    MO_URL="https://translate.wordpress.org/projects/wp-plugins/meta-box/dev/$LANGUAGE_NAME/default/export-translations/?format=mo"

    curl -s -o "${OUTPUT_DIR}/${LANGUAGE_NAME}.mo" $MO_URL
    echo "Downloaded: ${LANGUAGE_NAME}.mo"
done

echo "All translations downloaded."
