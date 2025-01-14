#!/bin/bash

# URL cơ sở của dự án Meta Box trên translate.wordpress.org
BASE_URL="https://translate.wordpress.org/projects/wp-plugins/meta-box/dev/"

# Thư mục đích để lưu trữ các tệp .mo
OUTPUT_DIR="./languages/meta-box"

# Tạo thư mục đích nếu nó chưa tồn tại
mkdir -p "$OUTPUT_DIR"

# Bảng ánh xạ giữa tên locale và mã ngôn ngữ trong URL
declare -A LANG_MAP
LANG_MAP["bg_BG"]="bg"
LANG_MAP["bn_BD"]="bn"
LANG_MAP["cs_CZ"]="cs"
LANG_MAP["da_DK"]="da"
LANG_MAP["de_DE"]="de"
LANG_MAP["es_ES"]="es"
LANG_MAP["fr_FR"]="fr"
LANG_MAP["gl_ES"]="gl"
LANG_MAP["he_IL"]="he"
LANG_MAP["hu_HU"]="hu"
LANG_MAP["id_ID"]="id"
LANG_MAP["it_IT"]="it"
LANG_MAP["ko_KR"]="ko"
LANG_MAP["lt_LT"]="lt"
LANG_MAP["mk_MK"]="mk"
LANG_MAP["ms_MY"]="ms"
LANG_MAP["nb_NO"]="nb"
LANG_MAP["nl_NL"]="nl"
LANG_MAP["pl_PL"]="pl"
LANG_MAP["pt_BR"]="pt-br"
LANG_MAP["pt_PT"]="pt"
LANG_MAP["ro_RO"]="ro"
LANG_MAP["ru_RU"]="ru"
LANG_MAP["sk_SK"]="sk"
LANG_MAP["sl_SI"]="sl"
LANG_MAP["sr_RS"]="sr"
LANG_MAP["sv_SE"]="sv"
LANG_MAP["tr_TR"]="tr"
LANG_MAP["zh_CN"]="zh-cn"
LANG_MAP["zh_TW"]="zh-tw"

# Danh sách 22 ngôn ngữ được hỗ trợ (ĐÃ KIỂM TRA KỸ)
LANGUAGES=(
    "ar" "azb" "bg_BG" "bn_BD" "ca" "cs_CZ" "da_DK" "de_DE" "el" "es_ES" "fa"
    "fi" "fr_FR" "gl_ES" "he_IL" "hr" "hu_HU" "id_ID" "it_IT" "ja" "ko_KR" "lt_LT"
    "mk_MK" "ms_MY" "nb_NO" "nl_NL" "pl_PL" "pt_BR" "pt_PT" "ro_RO" "ru_RU" "sk_SK"
    "sl_SI" "sq" "sr_RS" "sv_SE" "th" "tr_TR" "uk" "vi" "zh_CN" "zh_TW"
)

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
    FILENAME="meta-box-$LANG.l10n.php"

    # Tải file .mo
    curl -s -o "$OUTPUT_DIR/$FILENAME" "$DOWNLOAD_URL"

    if [[ $? -eq 0 ]]; then
        echo "Đã tải xuống file PHP cho ngôn ngữ: $LANG"
    else
        echo "Lỗi khi tải xuống file PHP cho ngôn ngữ: $LANG"
    fi

    sleep 5
done

echo "Hoàn tất."