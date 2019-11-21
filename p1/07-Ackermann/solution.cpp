#include <iostream>
using namespace std;

/* Ackermann function:
 A(m,n) 
 = n+1	   if m = 0
 = A(m-1,1)	   if m > 0 and n = 0
 = A(m-1,A(m,n-1)) if m > 0 and n > 0
 */
int ack(int m, int n)
{
	if( m == 0 )
		return n+1;
	if( n == 0 )
		return ack(m-1,1);
	return ack(m-1,ack(m,n-1));
}

int main()
{
	int m, n;
	cout << "* Ackermann *\n";
	cout << "Enter m: "; cin >> m;
	cout << "Enter n: "; cin >> n;
	cout << endl;
	cout << "Ackermann(" << m << "," << n << ") = " << ack(m,n) << endl;
	
}

