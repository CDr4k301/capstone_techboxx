import json
import sys
from sqlalchemy import create_engine
import pandas as pd
from mlxtend.frequent_patterns import apriori, association_rules
from mlxtend.preprocessing import TransactionEncoder

# CONNECT DB
username = "root"
password = "S34_dr4k3"
host = "127.0.0.1"
port = 3306
database = "techboxx"

# ---------- DB CONNECTION ----------
engine = create_engine(f"mysql+pymysql://{username}:{password}@{host}:{port}/{database}")

# ---------- BUILD LOOKUP MAPS ----------
# CPU
cpu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price FROM cpus", engine)
cpu_map = {row.id: f"{row.brand} {row.model} {row.build_category_id} {row.price}" for row in cpu_df.itertuples()}

# GPU
gpu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price FROM gpus", engine)
gpu_map = {row.id: f"{row.brand} {row.model} {row.build_category_id} {row.price}" for row in gpu_df.itertuples()}

# RAM
ram_df = pd.read_sql("SELECT id, brand, model, build_category_id, price FROM rams", engine)
ram_map = {row.id: f"{row.brand} {row.model} {row.build_category_id} {row.price}" for row in ram_df.itertuples()}

# Motherboard
mobo_df = pd.read_sql("SELECT id, brand, model, build_category_id, price FROM motherboards", engine)
mobo_map = {row.id: f"{row.brand} {row.model} {row.build_category_id} {row.price}" for row in mobo_df.itertuples()}

# PSU
psu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price FROM psus", engine)
psu_map = {row.id: f"{row.brand} {row.model} {row.build_category_id} {row.price}" for row in psu_df.itertuples()}

# Case
case_df = pd.read_sql("SELECT id, brand, model, build_category_id, price FROM pc_cases", engine)
case_map = {row.id: f"{row.brand} {row.model} {row.build_category_id} {row.price}" for row in case_df.itertuples()}

# Cooler
cooler_df = pd.read_sql("SELECT id, brand, model, build_category_id, price FROM coolers", engine)
cooler_map = {row.id: f"{row.brand} {row.model} {row.build_category_id} {row.price}" for row in cooler_df.itertuples()}

# Storage
storage_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, storage_type FROM storages", engine)
storage_map = {row.id: f"{row.brand} {row.model}{row.storage_type}  {row.build_category_id} {row.price}" for row in storage_df.itertuples()}

lookup_maps = {
    "cpu": cpu_map,
    "gpu": gpu_map,
    "ram": ram_map,
    "psu": psu_map,
    "cooler": cooler_map,
    "motherboard": mobo_map,
    "pc_case": case_map,
    "storage": storage_map
}

category_mapping = {
    "general use": 1,
    "gaming": 2,
    "graphics intensive": 3
}

cpu_brands = ["AMD", "Intel"]

# Parse arguments
if len(sys.argv) >= 2:
    category_input = sys.argv[1].lower()
    best_category = category_mapping.get(category_input)
else:
    best_category = None

preferred_cpu_brand = None
if len(sys.argv) >= 3:
    cpu_input = sys.argv[2].lower()
    if cpu_input == "amd":
        preferred_cpu_brand = "AMD"
    elif cpu_input == "intel":
        preferred_cpu_brand = "Intel"
    else:
        preferred_cpu_brand = None

# TWEAK HERE: Set user budget for component-based recommendations
user_budget = None  # Set to None for no budget limit

# Parse budget from command line (4th argument)
if len(sys.argv) >= 4:
    try:
        user_budget = float(sys.argv[3])
    except ValueError:
        user_budget = None

# ---------- CREATE CATEGORY AND PRICE LOOKUP MAPS ----------
component_to_category = {}
component_to_brand = {}
component_to_price = {}  # New: track component prices
component_to_id = {}

for comp_type, comp_map in lookup_maps.items():
    for comp_id, comp_info in comp_map.items():
        # Extract info from the string: brand model category price
        parts = comp_info.split()
        category_id = int(parts[-2])  # Second to last is category
        price = float(parts[-1])      # Last is price
        brand = parts[0]              # First part is the brand
        
        component_key = f"{comp_type}_id:{comp_id}"
        component_to_category[component_key] = category_id
        component_to_brand[component_key] = brand
        component_to_price[component_key] = price
        component_to_id[component_key] = comp_id  # Store the component ID

# ---------- LOAD USER BUILDS ----------
query = "SELECT * FROM user_builds"
df = pd.read_sql(query, engine)

transactions = []
for _, row in df.iterrows():
    items = []
    for col in ["pc_case_id", "motherboard_id", "cpu_id", "gpu_id", "storage_id", "ram_id", "psu_id", "cooler_id"]:
        if pd.notna(row[col]):
            items.append(f"{col}:{row[col]}")
    transactions.append(items)

# ---------- APRIORI ----------
te = TransactionEncoder()
te_ary = te.fit(transactions).transform(transactions)
basket = pd.DataFrame(te_ary, columns=te.columns_)

frequent_itemsets = apriori(basket, min_support=0.01, use_colnames=True)
rules = association_rules(frequent_itemsets, metric="confidence", min_threshold=0.6)

# ---------- GROUP BY BUILD CATEGORY ----------
def get_best_recommendation_by_category():
    # Get all possible component combinations from frequent itemsets
    component_types = ["pc_case_id", "motherboard_id", "cpu_id", "gpu_id", "storage_id", "ram_id", "psu_id", "cooler_id"]
    
    # Score each build category based on frequent itemsets
    category_scores = {}
    
    for _, itemset_row in frequent_itemsets.iterrows():
        itemset = itemset_row['itemsets']
        support = itemset_row['support']
        
        # Get categories for items in this itemset
        categories_in_set = []
        for item in itemset:
            if item in component_to_category:
                categories_in_set.append(component_to_category[item])
        
        if categories_in_set:
            # If all items in the set are from the same category, boost that category's score
            unique_categories = set(categories_in_set)
            for category in unique_categories:
                if category not in category_scores:
                    category_scores[category] = 0
                category_purity = categories_in_set.count(category) / len(categories_in_set)
                category_scores[category] += support * category_purity
    
    # Find the best category
    if not category_scores:
        return get_fallback_recommendations()
    
    # Use the globally set best_category if available, otherwise use automatic selection
    selected_category = best_category if best_category else max(category_scores, key=category_scores.get)
    
    # Now get the best component for each type within this category
    recommendations = {}
    
    for col in component_types:
        comp_type = col.replace("_id", "")
        
        category_items = []
        
        for _, itemset_row in frequent_itemsets.iterrows():
            itemset = itemset_row['itemsets']
            support = itemset_row['support']
            
            for item in itemset:
                if item.startswith(col) and item in component_to_category:
                    if component_to_category[item] == selected_category:
                        if comp_type == "cpu" and preferred_cpu_brand:
                            if item in component_to_brand:
                                item_brand = component_to_brand[item]
                                if item_brand.lower() != preferred_cpu_brand.lower():
                                    continue  # Skip this CPU, wrong brand
                        
                        category_items.append((item, support))
        
        if category_items:
            best_item = max(category_items, key=lambda x: x[1])[0]
            comp, comp_id = best_item.split(":")
            comp_id = int(comp_id)

            # Get details from lookup map
            details = lookup_maps[comp_type].get(comp_id, {})
            
            full_name = lookup_maps[comp_type].get(comp_id, f"Unknown {comp_id}")
            clean_name = " ".join(full_name.split()[:-2])  # Clean name without category and price
            
            # Get the price for the component
            price = component_to_price.get(f"{comp_type}_id:{comp_id}", 0)
            
            # Special handling for storage to include its type (SSD/HDD)
            if comp_type == "storage":
                storage_info = storage_map.get(comp_id, "").lower()
                if "ssd" in storage_info:
                    storage_type = "ssd"
                elif "hdd" in storage_info:
                    storage_type = "hdd"
                else:
                    storage_type = "unknown"

                recommendations["storage"] = {
                    "id": comp_id,
                    "name": clean_name,
                    "price": price,
                    "type": storage_type
                }

            else:
                recommendations[comp_type] = {
                    "id": comp_id,
                    "name": clean_name,
                    "price": price
                }
        else:
            fallback_item = get_fallback_for_component(comp_type, selected_category)
            recommendations[comp_type] = {
                "id": None,
                "name": fallback_item,
                "price": 0
            }
    
    return recommendations



def get_recommendations_for_category(target_category, preferred_cpu_brand=None):
    recommendations = {}
    component_types = ["pc_case_id", "motherboard_id", "cpu_id", "gpu_id", "storage_id", "ram_id", "psu_id", "cooler_id"]
    
    for col in component_types:
        comp_type = col.replace("_id", "")
        category_items = []
        
        for _, itemset_row in frequent_itemsets.iterrows():
            itemset = itemset_row['itemsets']
            support = itemset_row['support']
            
            for item in itemset:
                if item.startswith(col) and item in component_to_category:
                    if component_to_category[item] == target_category:
                        if comp_type == "cpu" and preferred_cpu_brand:
                            if item in component_to_brand:
                                item_brand = component_to_brand[item]
                                if item_brand.lower() != preferred_cpu_brand.lower():
                                    continue
                        category_items.append((item, support))
        
        if category_items:
            best_item = max(category_items, key=lambda x: x[1])[0]
            comp, comp_id = best_item.split(":")
            comp_id = int(comp_id)
            
            full_name = lookup_maps[comp_type].get(comp_id, f"Unknown {comp_id}")
            clean_name = " ".join(full_name.split()[:-2])
            price = component_to_price.get(f"{comp_type}_id:{comp_id}", 0)
            
            if comp_type == "storage":
                storage_info = storage_map.get(comp_id, "").lower()
                if "ssd" in storage_info:
                    storage_type = "ssd"
                elif "hdd" in storage_info:
                    storage_type = "hdd"
                else:
                    storage_type = "unknown"

                recommendations["storage"] = {
                    "id": comp_id,
                    "name": clean_name,
                    "price": price,
                    "type": storage_type
                }

            else:
                recommendations[comp_type] = {
                    "id": comp_id,
                    "name": clean_name, 
                    "price": price}
        else:
            fallback_item = get_fallback_for_component(comp_type, target_category)
            recommendations[comp_type] = {
                "id": None,
                "name": fallback_item, 
                "price": 0}
    
    return recommendations

def get_budget_recommendations(user_budget, target_category=None, preferred_cpu_brand=None, budget_tolerance=0.05):
    # print(f"User Budget: {user_budget:,.2f}")
    
    if target_category:
        initial_recommendations = get_recommendations_for_category(target_category, preferred_cpu_brand)
    else:
        initial_recommendations = get_best_recommendation_by_category()
    
    component_details = {}
    total_price = 0
    component_types = ["pc_case", "motherboard", "cpu", "gpu", "storage", "ram", "psu", "cooler"]
    
    for comp_type in component_types:
        if comp_type in initial_recommendations and initial_recommendations[comp_type]:
            rec = initial_recommendations[comp_type]
            comp_name = rec["name"] if isinstance(rec, dict) else rec
            comp_id = find_component_id_by_name(comp_type, comp_name)
            if comp_id:
                price = component_to_price.get(f"{comp_type}_id:{comp_id}", 0)
                category = component_to_category.get(f"{comp_type}_id:{comp_id}", 0)
                rec = initial_recommendations[comp_type]
                comp_name = rec["name"] if isinstance(rec, dict) else rec

                component_details[comp_type] = {
                    "name": comp_name,
                    "id": comp_id,
                    "price": price,
                    "category": category
                }

                total_price += price
    
    # print(f"Initial total price: {total_price:,.2f}")
    
    if total_price > user_budget:
        # print(f"Over budget by {total_price - user_budget:,.2f}, finding cheaper alternatives...")
        sorted_components = sorted(component_details.items(), key=lambda x: x[1]["price"], reverse=True)
        for comp_type, details in sorted_components:
            if total_price <= user_budget * (1 + budget_tolerance):
                break
            cheaper_alternative = find_cheaper_alternative(
                comp_type, details["category"], details["price"],
                preferred_cpu_brand if comp_type == "cpu" else None
            )
            if cheaper_alternative:
                savings = details["price"] - cheaper_alternative["price"]
                if savings > 0:
                    # print(f"  Replacing {comp_type}: {details['name']} ({details['price']:,.2f})  {cheaper_alternative['name']} ({cheaper_alternative['price']:,.2f}) [Save {savings:,.2f}]")
                    component_details[comp_type] = cheaper_alternative
                    total_price -= savings
    
    final_recommendations = {}
    final_total = 0
    for comp_type in component_types:
        if comp_type in component_details:
            detail = component_details[comp_type]
            if comp_type == "storage":
                storage_info = storage_map.get(detail["id"], "").lower()
                if "ssd" in storage_info:
                    storage_type = "ssd"
                elif "hdd" in storage_info:
                    storage_type = "hdd"
                else:
                    storage_type = "unknown"

                final_recommendations["storage"] = {
                    "id": detail["id"],
                    "name": detail["name"],
                    "price": detail["price"],
                    "type": storage_type
                }

            else:
                final_recommendations[comp_type] = {
                    "id": detail["id"],
                    "name": detail["name"], 
                    "price": detail["price"]}
            final_total += detail["price"]
        else:
            final_recommendations[comp_type] = {
                "id": None,
                "name": None, 
                "price": 0}
    
    final_recommendations["budget_summary"] = {
        "user_budget": user_budget,
        "total_price": final_total,
        "remaining_budget": user_budget - final_total,
        "within_budget": final_total <= user_budget * (1 + budget_tolerance)
    }
    
    return final_recommendations


def find_component_id_by_name(comp_type, comp_name):
    """Find component ID by matching the name"""
    comp_map = lookup_maps[comp_type]
    for comp_id, comp_info in comp_map.items():
        # Remove category and price from the end to get clean name
        clean_name = " ".join(comp_info.split()[:-2])
        if clean_name.strip().lower() == comp_name.strip().lower():
            return comp_id

    return None

def find_cheaper_alternative(comp_type, target_category, max_price, preferred_brand=None):
    """Find a cheaper component in the same category"""
    comp_map = lookup_maps[comp_type]
    alternatives = []
    
    for comp_id, comp_info in comp_map.items():
        parts = comp_info.split()
        category_id = int(parts[-2])
        price = float(parts[-1])
        brand = parts[0]
        
        # Must be same category, cheaper, and optionally match brand
        if (category_id == target_category and 
            price < max_price and
            (not preferred_brand or brand.lower() == preferred_brand.lower())):
            
            clean_name = " ".join(parts[:-2])
            alternatives.append({
                "name": clean_name,
                "id": comp_id,
                "price": price,
                "category": category_id
            })
    
    # Return the most expensive among the cheaper options (best value)
    if alternatives:
        return max(alternatives, key=lambda x: x["price"])
    
    return None

def get_fallback_for_component_with_brand(comp_type, target_category, preferred_brand):
    """Get any component of the given type from the target category with specific brand"""
    comp_map = lookup_maps[comp_type]
    for comp_id, comp_info in comp_map.items():
        parts = comp_info.split()
        category_id = int(parts[-2])
        brand = parts[0]
        
        if category_id == target_category and brand.lower() == preferred_brand.lower():
            return " ".join(parts[:-2])  # Remove category number and price
    
    # If no match found with preferred brand, fall back to any brand in the category
    # print(f"Warning: No {preferred_brand} {comp_type} found in category {target_category}, using any brand")
    return get_fallback_for_component(comp_type, target_category)

def get_fallback_for_component(comp_type, target_category):
    """Get any component of the given type from the target category"""
    comp_map = lookup_maps[comp_type]
    for comp_id, comp_info in comp_map.items():
        parts = comp_info.split()
        category_id = int(parts[-2])  # Second to last is category
        if category_id == target_category:
            return " ".join(parts[:-2])  # Remove category number and price
    return None

def get_fallback_recommendations():
    recommendations = {}
    component_types = ["pc_case_id", "motherboard_id", "cpu_id", "gpu_id", "storage_id", "ram_id", "psu_id", "cooler_id"]
    
    for col in component_types:
        filtered = frequent_itemsets[frequent_itemsets['itemsets'].astype(str).str.contains(col)]
        if not filtered.empty:
            top_item = filtered.sort_values("support", ascending=False).iloc[0]
            item = list(top_item['itemsets'])[0]
            comp, comp_id = item.split(":")
            comp_type = comp.replace("_id", "")
            comp_id = int(comp_id)
            
            full_name = lookup_maps[comp_type].get(comp_id, f"Unknown {comp_id}")
            clean_name = " ".join(full_name.split()[:-2])
            
            if comp_type == "storage":
                storage_info = storage_map.get(comp_id, "").lower()
                if "ssd" in storage_info:
                    storage_type = "ssd"
                elif "hdd" in storage_info:
                    storage_type = "hdd"
                else:
                    storage_type = "unknown"

                recommendations["storage"] = {
                    "id": comp_id,
                    "name": clean_name,
                    "price": component_to_price.get(f"{comp_type}_id:{comp_id}", 0),
                    "type": storage_type
                }
            else:
                recommendations[comp_type] = {
                    "id": comp_id,
                    "name": clean_name,
                    "price": component_to_price.get(f"{comp_type}_id:{comp_id}", 0)
                }

        else:
            recommendations[col.replace("_id", "")] = {None}
    
    return recommendations


# ---------- GET RECOMMENDATIONS ----------
# BUDGET-BASED RECOMMENDATIONS (calculates individual component prices)
if user_budget:
    if best_category and preferred_cpu_brand:
        recommendations = get_budget_recommendations(user_budget, best_category, preferred_cpu_brand)
    elif best_category:
        recommendations = get_budget_recommendations(user_budget, best_category)
    else:
        recommendations = get_budget_recommendations(user_budget)
else:
    # ORIGINAL METHOD (no budget consideration)
    if best_category and preferred_cpu_brand:
        recommendations = get_recommendations_for_category(best_category, preferred_cpu_brand)
    elif best_category:
        recommendations = get_recommendations_for_category(best_category)
    else:
        recommendations = get_best_recommendation_by_category()

# ---------- OUTPUT JSON ----------
print(json.dumps(recommendations, indent=4, ensure_ascii=False))