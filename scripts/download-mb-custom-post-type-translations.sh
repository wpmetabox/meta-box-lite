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

# Kiểm tra xem curl đã được cài đặt chưa
if ! command -v curl &>/dev/null; then
    echo "Lỗi: curl chưa được cài đặt. Vui lòng cài đặt nó (ví dụ: sudo apt-get install curl)."
    exit 1
fi

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
done

echo "Hoàn tất."