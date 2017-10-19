import mysql.connector
import pandas as pd
import numpy as np
from sklearn.neighbors import NearestNeighbors

# connect to database
cnx = mysql.connector.connect(user='', password='',
                              host='',
                              database='')
cursor = cnx.cursor()

# retrieve user data
users = pd.read_sql("SELECT `user_id`,`keyword`, `frequency` FROM `users`", con=cnx) 

# convert data to user / keyword array
user_array = pd.crosstab(users.user_id, users.keyword, users.frequency, aggfunc=np.sum).fillna(0)

# normalize by user
user_array_norm = user_array.apply(lambda x: (x)/(x.max()), axis=1)

# find nearest neighbors
neigh = NearestNeighbors(n_neighbors=2)
neigh.fit(user_array_norm)
neigh_array = neigh.kneighbors(user_array_norm)

# calculate averages based on neighbors
collab_array = pd.DataFrame(data=None, columns=user_array_norm.columns, index=user_array_norm.index)

for i in range(len(user_array_norm)):
    collab_array.iloc[i] = np.average([user_array_norm.iloc[neigh_array[1][i][0]],
                                       user_array_norm.iloc[neigh_array[1][i][1]]], axis=0)

# flatten array
collab_array = collab_array.reset_index()
collab_array_flat = pd.melt(collab_array, id_vars = 'user_id', var_name = 'keyword', value_name = 'frequency')
collab_array_flat = collab_array_flat.drop(collab_array_flat[collab_array_flat.frequency == 0].index)
collab_array_flat = collab_array_flat.reset_index(drop=True)


#print(collab_array_flat)

# upload collaborations to database
cursor.execute("TRUNCATE TABLE `users_collab`")
for i in range(len(collab_array_flat)):
    cursor.execute("INSERT into `users_collab` (`user_id`, `keyword`, `frequency`) VALUES (%s, %s, %s)" , (int(collab_array_flat.user_id[i]), collab_array_flat.keyword[i], float(collab_array_flat.frequency[i])))
    

cnx.close()


print("Collaboration complete!")
