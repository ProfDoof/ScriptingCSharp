#include <iostream>
#include <fstream>
#include <sstream>
#include <map>
#include <vector>
#include <list>
#include <set>
#include <algorithm>
#if WIN32
    #define WIN32_LEAN_AND_MEAN 1
    #include <windows.h>
#endif
using namespace std;

struct Location {
    Location(const string &c,int p,int l) : chapter(c),paragraph(p),line(l),count(1) {}
    
    string chapter;
    int paragraph;
    int line;
    int count;
};

typedef map<string,string> Translate;
typedef vector<Location> Locations;
typedef map<string,Locations> Index;
typedef map<string,int> ChapterCounts;

void foo() {}

string strip1(const string &s)
{
    //if (s.empty()) 
    //    return string("");
        
    static string punctuation = ".?!,;:-\"'_()";
    
    auto b=s.begin();
    while (punctuation.find(*b)!=string::npos)
        b++;

    auto e=s.end();
    if (e!=b) {
        e--;
        while (e!=b 
            && punctuation.find(*e)!=string::npos)
            e--;
        e++;
    }
    else
        foo();
    
    string result(b,e);
    transform(result.begin(), result.end(), result.begin(), ::tolower);
    //cout << result << endl;
    return result;
}

string strip2(const string &s)
{
    //if (s.empty()) 
    //    return string("");
    
    static string punctuation = ".?!,;:-\"'_()";
    
    auto b=s.begin();
    while (punctuation.find(*b)!=string::npos)
        b++;

    auto e=s.end();
    if (e!=b) {
        e--;
        while (e!=b 
            && punctuation.find(*e)!=string::npos)
            e--;
        e++;
    }
    else
        foo();
    
    string result(b,e);
    transform(result.begin(), result.end(), result.begin(), ::tolower);
    //cout << result << endl;
    return result;
}

string translate1(const string &word,const Translate &synonyms)
{
    auto i=synonyms.find(word);
    if (i!=synonyms.end())
        return i->second;
    return word;
}

string translate2(const string &word,const Translate &synonyms)
{
    auto i=synonyms.find(word);
    if (i!=synonyms.end())
        return i->second;
    return word;
}

string translate3(const string &word,const Translate &synonyms)
{
    auto i=synonyms.find(word);
    if (i!=synonyms.end())
        return i->second;
    return word;
}

void fill_ignore_words(const char *filename,Translate &synonyms)
{
    fstream file(filename);
    string word;
    while (file >> word) {
        string w = strip1(word);
        if (w != "")
            synonyms[w] = string("");
        else
            foo();
    }
}

void get_words1(const string &line,list<string> &words)
{
    stringstream ss(line);
    string word;
    while (ss >> word) {
        word = strip2(word);
        if (word != "")
            words.push_back(word);
        else 
            foo();
    }
}    

void get_words2(const string &line,list<string> &words)
{
    stringstream ss(line);
    string word;
    while (ss >> word) {
        word = strip2(word);
        if (word != "")
            words.push_back(word);
        else 
            foo();
    }
}    

void fill_synonym_words(const char *filename,Translate &synonyms)
{
    fstream file(filename);
    string line;
    while (!file.eof()) {
        getline(file,line);
        list<string> words;
        get_words1(line,words);
        if (words.empty())
            foo();
        else {
            string to = translate1(words.front(),synonyms); 
            words.pop_front();
            if (words.empty())
                foo();
            while (!words.empty()) {
                string from = translate2(words.front(),synonyms);
                if (from != "") 
                    synonyms[from] = to;
                else 
                    foo();
                words.pop_front();
            }
        }
    }
}

bool lower_count(const ChapterCounts::value_type &a,const ChapterCounts::value_type &b)
{
    if (a.second < b.second)
        return true;
    return false;
}

int main(int argc,const char *argv[])
{
#if WIN32
    DWORD dwMode = SetErrorMode(SEM_NOGPFAULTERRORBOX);
    SetErrorMode(dwMode | SEM_NOGPFAULTERRORBOX);
#endif

    if (argc != 4) { 
        cout << "usage: index <book> <ignore> <synonyms>\n";
        return 0;
    }

    Translate synonyms;
    fill_ignore_words(argv[2],synonyms);
    fill_synonym_words(argv[3],synonyms);

    Location current("0",0,0);
    bool skipping_blanks = true;
    Index index;

    fstream file(argv[1]);
    while (!file.eof()) {
        string line;
        getline(file,line);
        current.line++;
        stringstream ss(line);
        list<string> words;
        get_words2(line,words);
        if (words.size()==2 
        && words.front()=="chapter") {
            words.pop_front();
            current.chapter = words.front();
            current.paragraph = current.line = 0;
            skipping_blanks = true;
        }
        else if (words.empty())
            skipping_blanks = true; // blank line -- do nothing
        else {
            if (skipping_blanks) {
                current.paragraph++;
                current.line = 1;
                skipping_blanks = false;
            }
            else 
                foo();
            while (!words.empty()) {
                string word = translate3(words.front(),synonyms);
                if (word != "") {
                    vector<Location> &v = index[word];
                    if (!v.empty() 
                    && v.back().chapter==current.chapter 
                    && v.back().paragraph==current.paragraph)
                        v.back().count++;
                    else 
                        v.push_back(current);
                }
                words.pop_front();
            }
        }
    }
    
    for (auto word=index.begin(); 
        word!=index.end(); 
        ++word) {
        ChapterCounts counts;
        for (auto i=word->second.begin(); 
            i!=word->second.end(); 
            ++i) {    
            int sum = i->count;
            if (counts.find(i->chapter)!=counts.end())
                sum += counts[i->chapter];
            else
                foo();
            counts[i->chapter] = sum;
        }
        auto max = max_element(counts.begin(),counts.end(),lower_count);
        stringstream priority;
        stringstream rest;
        for (auto loc=word->second.begin(); loc!=word->second.end(); ++loc)
            if (loc->chapter==max->first) {
                if (priority.tellp()==0)
                    foo();
                else
                    priority << ",";
                priority << " " << loc->chapter << ":P" << loc->paragraph << "L" << loc->line;
            }
            else {
                if (rest.tellp()==0)
                    foo();
                else
                    rest << ",";
                rest << " " << loc->chapter << ":P" << loc->paragraph << "L" << loc->line;
            }
        cout << word->first << priority.str();
        if (!rest.str().empty())
            cout << "," << rest.str();
        else
            foo();
        cout << "\n";
    }
}
