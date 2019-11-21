#include <iostream>
using namespace std;

int main()
{
	int hw, e1, e2, e3, fe;
	int grade;
	
	cout << "Homework: "; cin >> hw;
	cout << "Exam #1: "; cin >> e1;
	cout << "Exam #2: "; cin >> e2;
	cout << "Exam #3: "; cin >> e3;
	cout << "Final Exam: "; cin >> fe;
	
	grade = (int) ((hw + e1 + e2 + e3 + 2*fe)/6);
	
	cout << endl << "Overall grade: " << grade << endl;
}