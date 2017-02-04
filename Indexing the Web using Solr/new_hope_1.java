import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

public class new_hope_1 {

	public static void main(String[] args) throws IOException {
		// TODO Auto-generated method stub
		
		// TO DO : Generate a file called edgeList.txt 
		
		// Local variables
		String csvFile1 = "/Users/NamanAvasthi/Documents/USC/Fall 2016/IR/Assignments/3/crawl_data_example/mastercsvfile.csv";
//		String csvFile2 = "/Users/NamanAvasthi/Documents/USC/Fall 2016/IR/Assignments/3/crawl_data_example/map_wsj.csv";
		String dirPath = "/Users/NamanAvasthi/Documents/USC/Fall 2016/IR/Assignments/3/crawl_data_example/AllNewsData";
		String line;
		String cvsSplitBy = ",";
		
		int count = 0;
		
		
		
		// Creating the two map* files !
		
		print("Creating Map* files !");
		
		Map<String, String> fileUrlMap = new HashMap<String,String>();
        Map<String, String> urlFileMap = new HashMap<String,String>();
        
        try (BufferedReader br = new BufferedReader(new FileReader(csvFile1))) {

            while ((line = br.readLine()) != null) {

                // use comma as separator
                String[] data = line.split(cvsSplitBy);
                fileUrlMap.put(data[0], data[1]);
                urlFileMap.put(data[1], data[0]);
            }

        } catch (IOException e) {
            e.printStackTrace();
        }
        
//        try (BufferedReader br = new BufferedReader(new FileReader(csvFile2))) {
//
//            while ((line = br.readLine()) != null) {
//
//                // use comma as separator
//                String[] data = line.split(cvsSplitBy);
//                fileUrlMap.put(data[0], data[1]);
//                urlFileMap.put(data[1], data[0]);
//            }
//
//        } catch (IOException e) {
//            e.printStackTrace();
//        }
        
        print("Done Creating Map* files !");
    
        // End of creation of map* files !

        
        
        
//        print(" Data : %s ",urlFileMap);
        
        
        
        // Creating the Web Graph Structure
        
        print("Creating Edges Data Structure !");
        
        File dir = new File(dirPath);
        Set<String> edges = new HashSet<String>();
        
        for(File file: dir.listFiles()) {
        	
        	Document doc = Jsoup.parse(file , "UTF-8" , fileUrlMap.get(file.getName()));
        	Elements links = doc.select("a[href]");
        	Elements pngs = doc.select("[src]");
        	
        	for(Element link: links) {
        		
        		String url = link.attr("href").trim();
        		
        		if(urlFileMap.containsKey(url)) {
        			edges.add(file.getName() + " " + urlFileMap.get(url));
        			
//        			print("working on it... %d",count);
//        			count++;
        			
        		}
        		
        	}
        	
        }
        
//        for(String s:edges) {
//        	print(" Data : %s",s);
//        }
        
        print("Done Creating Edges Data Structure !");
        
        // End of Web Graph Structure creation
        
        
        
        
        // Creating a text file for edges 
        
        print("Started to make edgeList.txt !");
        
        try (PrintWriter out = new PrintWriter("edgeList.txt") ) {
        	for (String s: edges)
        		out.println(s);
        }
        
        print("Done Creating edgeList.txt !");
        
        // edgeList.txt created

		
	}
	
	private static void print(String msg,Object... args) {
		System.out.println(String.format(msg, args));
	}

}
