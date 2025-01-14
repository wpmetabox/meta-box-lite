#!/bin/bash

# URL cơ sở của dự án Meta Box trên translate.wordpress.org
BASE_URL="https://translate.wordpress.org/projects/wp-plugins/mb-custom-post-type/dev/"

# Thư mục đích để lưu trữ các tệp .mo
OUTPUT_DIR="./languages/mb-custom-post-type"

# Tạo thư mục đích nếu nó chưa tồn tại
mkdir -p "$OUTPUT_DIR"

# Danh sách 3 ngôn ngữ được hỗ trợ (ĐÃ KIỂM TRA KỸ)
LANGUAGES=(
    "zh_TW" "pt_BR" "vi"
)

# md5(modules/mb-custom-post-type/assets/build/post-type.js)
MD5_POST_TYPE="991f24d69fe8c7b94a2c47b36cb5d31e"

# md5(modules/mb-custom-post-type/assets/build/taxonomy.js)
MD5_TAXONOMY="58fd7ddfc95c317e1563ed5a87f9d1ed"

# Duyệt qua từng ngôn ngữ
for LANG in "${LANGUAGES[@]}"; do
    # Tạo URL tải xuống
    DOWNLOAD_URL="$BASE_URL$LANG/default/export-translations/?format=php"

    # Tạo tên file (.l10n.php)
    FILENAME="mb-custom-post-type-$LANG.l10n.php"

    # Tải file .mo
    curl -s -o "$OUTPUT_DIR/$FILENAME" "$DOWNLOAD_URL"

    if [[ $? -eq 0 ]]; then
        echo "Đã tải xuống: $FILENAME (từ $DOWNLOAD_URL)"
    else
        echo "Lỗi khi tải xuống: $FILENAME (từ $DOWNLOAD_URL)"
    fi

    # Tạo URL tải xuống file JSON
    DOWNLOAD_URL="$BASE_URL$LANG/default/export-translations/?format=jed1x"

    # Tạo tên file (.json)
    FILENAME="mb-custom-post-type-$LANG-$MD5_POST_TYPE.json"
    FILENAME2="mb-custom-post-type-$LANG-$MD5_TAXONOMY.json"

    # Tải file .mo
    curl -s -o "$OUTPUT_DIR/$FILENAME" "$DOWNLOAD_URL"

    if [[ $? -eq 0 ]]; then
        cp "$OUTPUT_DIR/$FILENAME" "$OUTPUT_DIR/$FILENAME2"
        echo "Đã tải xuống: $FILENAME (từ $DOWNLOAD_URL)"
    else
        echo "Lỗi khi tải xuống: $FILENAME (từ $DOWNLOAD_URL)"
    fi
done

echo "Hoàn tất."