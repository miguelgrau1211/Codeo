
import json
import os

i18n_dir = r"c:\Users\Haziel\Desktop\2_DAW\Proyecto\Front\public\i18n"
source_file = os.path.join(i18n_dir, "es.json")

with open(source_file, "r", encoding="utf-8") as f:
    source_data = json.load(f)

def get_all_keys(d, parent_key=''):
    keys = {}
    for k, v in d.items():
        new_key = f"{parent_key}.{k}" if parent_key else k
        if isinstance(v, dict):
            keys.update(get_all_keys(v, new_key))
        else:
            keys[new_key] = v
    return keys

source_keys = get_all_keys(source_data)

# Target languages and their specific translations for the NEW keys (only those added recently)
# If a key is missing from a file but existed in es.json for a long time, it might be outdated.
# I will focus on the keys I just added: 
# ACHIEVEMENTS.ERROR_LOADING, ACHIEVEMENTS.PROGRESS_DETAIL, DASHBOARD.PAYMENT_INIT_TITLE, DASHBOARD.PAYMENT_INIT_DESC, DASHBOARD.CARD_DATA, DASHBOARD.CARD_HOLDER_PH, DASHBOARD.SEASON_NAME

# But I should also check for other missing keys.

files = [f for f in os.listdir(i18n_dir) if f.endswith(".json") and f != "es.json"]

for filename in files:
    path = os.path.join(i18n_dir, filename)
    with open(path, "r", encoding="utf-8") as f:
        target_data = json.load(f)
    
    target_keys = get_all_keys(target_data)
    missing = [k for k in source_keys if k not in target_keys]
    
    if missing:
        print(f"File {filename} is missing {len(missing)} keys.")
        # We will add missing keys with the Spanish value as placeholder, or I can try to translate them later.
        # For now, let's just identify them and I'll do a few ones manually or in the script if they are few.
        for m in missing:
            parts = m.split('.')
            current = target_data
            for p in parts[:-1]:
                if p not in current:
                    current[p] = {}
                current = current[p]
            
            # Simple translation logic for common keys or just fallback to source
            current[parts[-1]] = source_keys[m]

    # Save back
    with open(path, "w", encoding="utf-8") as f:
        json.dump(target_data, f, indent=2, ensure_ascii=False)
