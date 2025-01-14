#!/bin/bash

# NOTE: mush run in bash
# bash scripts/download-mb-custom-post-type-translations.sh

# URL cơ sở của dự án Meta Box trên translate.wordpress.org
BASE_URL="https://translate.wordpress.org/projects/wp-plugins/mb-custom-post-type/dev/"

# Thư mục đích để lưu trữ các tệp .mo
OUTPUT_DIR="./languages/mb-custom-post-type"

# Tạo thư mục đích nếu nó chưa tồn tại
mkdir -p "$OUTPUT_DIR"

# Bảng ánh xạ giữa tên locale và mã ngôn ngữ trong URL
declare -A LANG_MAP
LANG_MAP["zh_TW"]="zh-tw"
LANG_MAP["pt_BR"]="pt-br"

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
    DOWNLOAD_LANG=${LANG_MAP[$LANG]}

    # Nếu không tìm thấy locale code, tiếp tục với ngôn ngữ tiếp theo
    if [ -z "$DOWNLOAD_LANG" ]; then
        DOWNLOAD_LANG=$LANG
    fi

    echo $LANG
    echo $DOWNLOAD_LANG

    # Tạo URL tải xuống
    DOWNLOAD_URL="$BASE_URL$DOWNLOAD_LANG/default/export-translations/?format=php"

    # Tạo tên file (.l10n.php)
    FILENAME="mb-custom-post-type-$LANG.l10n.php"

    # Tải file .mo
    curl -s -o "$OUTPUT_DIR/$FILENAME" "$DOWNLOAD_URL"

    if [[ $? -eq 0 ]]; then
        echo "Đã tải xuống file PHP cho ngôn ngữ: $LANG"
    else
        echo "Lỗi khi tải xuống file PHP cho ngôn ngữ: $LANG"
    fi

    # Tạo URL tải xuống file JSON
    DOWNLOAD_URL="$BASE_URL$DOWNLOAD_LANG/default/export-translations/?format=jed1x"

    # Tạo tên file (.json)
    FILENAME="mb-custom-post-type-$LANG-$MD5_POST_TYPE.json"
    FILENAME2="mb-custom-post-type-$LANG-$MD5_TAXONOMY.json"

    # Tải file .mo
    curl -s -o "$OUTPUT_DIR/$FILENAME" "$DOWNLOAD_URL"

    if [[ $? -eq 0 ]]; then
        cp "$OUTPUT_DIR/$FILENAME" "$OUTPUT_DIR/$FILENAME2"
        echo "Đã tải xuống file JSON cho ngôn ngữ: $LANG"
    else
        echo "Lỗi khi tải xuống file JSON cho ngôn ngữ: $LANG"
    fi
done

echo "Hoàn tất."