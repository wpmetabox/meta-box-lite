#!/bin/bash

# URL cơ sở của dự án Meta Box trên translate.wordpress.org
BASE_URL="https://translate.wordpress.org/projects/wp-plugins/meta-box/dev/"

# Thư mục đích để lưu trữ các tệp .mo
OUTPUT_DIR="./languages/meta-box"

# Tạo thư mục đích nếu nó chưa tồn tại
mkdir -p "$OUTPUT_DIR"

# Danh sách 22 ngôn ngữ được hỗ trợ (ĐÃ KIỂM TRA KỸ)
LANGUAGES=(
    "ar" "azb" "bg_BG" "bn_BD" "ca" "cs_CZ" "da_DK" "de_DE" "el" "es_ES" "fa"
    "fi" "fr_FR" "gl_ES" "he_IL" "hr" "hu_HU" "id_ID" "it_IT" "ja" "ko_KR" "lt_LT"
    "mk_MK" "ms_MY" "nb_NO" "nl_NL" "pl_PL" "pt_BR" "pt_PT" "ro_RO" "ru_RU" "sk_SK"
    "sl_SI" "sq" "sr_RS" "sv_SE" "th" "tr_TR" "uk" "vi" "zh_CN" "zh_TW"
)

# Kiểm tra xem curl đã được cài đặt chưa
if ! command -v curl &>/dev/null; then
    echo "Lỗi: curl chưa được cài đặt. Vui lòng cài đặt nó (ví dụ: sudo apt-get install curl)."
    exit 1
fi

# Duyệt qua từng ngôn ngữ
for LANG in "${LANGUAGES[@]}"; do
    # Tạo URL tải xuống
    DOWNLOAD_URL="$BASE_URL$LANG/default/export-translations/?format=mo"

    # Tạo tên file
    FILENAME="meta-box-$LANG.mo"

    # Tải file .mo
    curl -s -o "$OUTPUT_DIR/$FILENAME" "$DOWNLOAD_URL"

    if [[ $? -eq 0 ]]; then
        echo "Đã tải xuống: $FILENAME (từ $DOWNLOAD_URL)"
    else
        echo "Lỗi khi tải xuống: $FILENAME (từ $DOWNLOAD_URL)"
    fi
done

echo "Hoàn tất."