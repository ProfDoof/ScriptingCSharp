#include <iostream>
using namespace std;

int main()
{
	int m1, d1, y1; // date #1
	int m2, d2, y2; // date #2
	
	cout << "Enter date #1 (month day year): "; cin >> m1 >> d1 >> y1;
	cout << "Enter date #2 (month day year): "; cin >> m2 >> d2 >> y2;
	cout << endl;
	
	if( y1 < y2 || ( y1 == y2 && ( m1 < m2 || ( m1 == m2 && d1 < d2 ) ) ) )
		cout << m1 << "/" << d1 << "/" << y1 << " is earlier." << endl;
	else
		cout << m2 << "/" << d2 << "/" << y2 << " is earlier." << endl;
}