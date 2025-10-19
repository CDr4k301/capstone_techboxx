import json
import sys
import os
from sqlalchemy import create_engine
import pandas as pd
from mlxtend.frequent_patterns import apriori, association_rules
from mlxtend.preprocessing import TransactionEncoder

# Add the current directory to Python path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

# CONNECT DB
username = "root"
password = "S34_dr4k3"
host = "127.0.0.1"
port = 3306
database = "techboxx"

# ---------- DB CONNECTION ----------
engine = create_engine(f"mysql+pymysql://{username}:{password}@{host}:{port}/{database}")

# ---------- BUILD LOOKUP MAPS ----------
def load_component_maps():
    """Load all component data into lookup maps"""
    maps = {}
    
    # CPU
    cpu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM cpus", engine)
    cpu_map = {}
    for row in cpu_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        cpu_map[row.id] = {
            'name': clean_name,
            'type': 'cpu',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'cpus',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['cpu'] = cpu_map

    # GPU
    gpu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM gpus", engine)
    gpu_map = {}
    for row in gpu_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        gpu_map[row.id] = {
            'name': clean_name,
            'type': 'gpu',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'gpus',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['gpu'] = gpu_map

    # RAM
    ram_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM rams", engine)
    ram_map = {}
    for row in ram_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        ram_map[row.id] = {
            'name': clean_name,
            'type': 'ram',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'rams',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['ram'] = ram_map

    # Motherboard
    mobo_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM motherboards", engine)
    mobo_map = {}
    for row in mobo_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        mobo_map[row.id] = {
            'name': clean_name,
            'type': 'motherboard',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'motherboards',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['motherboard'] = mobo_map

    # PSU
    psu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM psus", engine)
    psu_map = {}
    for row in psu_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        psu_map[row.id] = {
            'name': clean_name,
            'type': 'psu',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'psus',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['psu'] = psu_map

    # Case
    case_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM pc_cases", engine)
    case_map = {}
    for row in case_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        case_map[row.id] = {
            'name': clean_name,
            'type': 'pc_case',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'pc_cases',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['pc_case'] = case_map

    # Cooler
    cooler_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM coolers", engine)
    cooler_map = {}
    for row in cooler_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        cooler_map[row.id] = {
            'name': clean_name,
            'type': 'cooler',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'coolers',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['cooler'] = cooler_map

    # Storage
    storage_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, storage_type, stock FROM storages", engine)
    storage_map = {}
    for row in storage_df.itertuples():
        clean_name = f"{row.brand} {row.model} {row.storage_type}"
        storage_map[row.id] = {
            'name': clean_name,
            'type': 'storage',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'storages',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['storage'] = storage_map

    return maps

def get_frequently_bought_together():
    """Get most frequently bought item pairs using association rules"""
    
    # Load component maps
    lookup_maps = load_component_maps()
    
    # Load user builds
    query = "SELECT * FROM user_builds"
    df = pd.read_sql(query, engine)
    
    # Prepare transactions with component names
    transactions = []
    component_details = {}  # Store component details by name
    
    for _, row in df.iterrows():
        items = []
        for col in ["pc_case_id", "motherboard_id", "cpu_id", "gpu_id", "storage_id", "ram_id", "psu_id", "cooler_id"]:
            if pd.notna(row[col]):
                comp_type_col = col.replace("_id", "")
                comp_id = int(row[col])
                
                if comp_type_col in lookup_maps and comp_id in lookup_maps[comp_type_col]:
                    comp_data = lookup_maps[comp_type_col][comp_id]
                    comp_name = comp_data['name']
                    
                    # Store component details
                    component_details[comp_name] = {
                        'name': comp_name,
                        'type': comp_data['type'],
                        'price': comp_data['price'],
                        'id': comp_id,
                        'image': comp_data['image'],
                        'table': comp_data['table'],
                        'stock': comp_data['stock']
                    }
                    
                    items.append(comp_name)
        
        if items:  # Only add non-empty transactions
            transactions.append(items)
    
    if len(transactions) < 5:
        return {"status": "error", "message": "Not enough transaction data for analysis"}
    
    # Run Market Basket Analysis
    try:
        te = TransactionEncoder()
        te_ary = te.fit(transactions).transform(transactions)
        basket = pd.DataFrame(te_ary, columns=te.columns_)
        
        # Adjust min_support based on data size
        min_support = max(0.05, 2.0 / len(transactions))
        
        # Get frequent itemsets
        frequent_itemsets = apriori(basket, min_support=min_support, use_colnames=True, verbose=0)
        
        # Generate association rules
        rules = association_rules(frequent_itemsets, metric="confidence", min_threshold=0.3)
        
        # Filter for 2-item sets (pairs)
        pair_rules = []
        for _, rule in rules.iterrows():
            antecedents = list(rule['antecedents'])
            consequents = list(rule['consequents'])
            
            # Only include rules with exactly 2 items total
            if len(antecedents) + len(consequents) == 2:
                item1 = list(antecedents)[0]
                item2 = list(consequents)[0]
                
                # Get component details
                if item1 in component_details and item2 in component_details:
                    pair_data = {
                        'product_a': item1,
                        'product_b': item2,
                        'total_price': float(component_details[item1]['price'] + component_details[item2]['price']),
                        'confidence': float(rule['confidence']),
                        'support': float(rule['support']),
                        'lift': float(rule['lift'])
                    }
                    pair_rules.append(pair_data)
        
        # Sort by confidence and support
        pair_rules.sort(key=lambda x: (x['confidence'], x['support']), reverse=True)
        
        # Get top pairs (limit to 5 for the table)
        top_pairs = pair_rules[:5]
        
        result = {
            "status": "success",
            "total_transactions": len(transactions),
            "frequentPairs": top_pairs
        }
        
        return result
        
    except Exception as e:
        return {"status": "error", "message": f"Analysis failed: {str(e)}"}

# ---------- MAIN EXECUTION ----------
if __name__ == "__main__":
    try:
        result = get_frequently_bought_together()
        
        if result["status"] == "success":
            # Format the output for Laravel consumption
            formatted_pairs = []
            for pair in result["frequentPairs"]:
                formatted_pairs.append({
                    'product_a': pair['product_a'],
                    'product_b': pair['product_b'],
                    'total_price': pair['total_price'],
                    'confidence': pair['confidence'],
                    'support': pair['support']
                })
            
            output = {
                "status": "success",
                "frequentPairs": formatted_pairs,
                "total_builds_analyzed": result["total_transactions"]
            }
        else:
            output = {"status": "error", "message": result["message"]}
            
        print(json.dumps(output))
        
    except Exception as e:
        print(json.dumps({"status": "error", "message": f"Script execution failed: {str(e)}"}))