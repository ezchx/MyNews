# MyNews

Custom news feed based on content preferences and collaborative filtering:

Article pre-processing:  
•	Read newsapi.org JSON feed  
•	Store article summary information in an SQL database  
•	Extract article keywords using RAKE  
•	Calculate article bias scores based on subjective word content  

User interface:  
•	User click on article  
•	Article keywords are stored in user profile  
•	Collaborative user profile is generated using KNN  
•	Article sorting is based on collaborative user profiles
