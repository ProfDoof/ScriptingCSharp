#include <cstdlib>
#include <cstring>
#include <iostream>
enum Type { MALLOC, NEW, NEW_ARRAY };
static int	listMax = 0; 
static int 	listSize = 0;
static void **	listPointer = 0;
static Type *	listType = 0;
static int *	listBytes = 0;

bool add(void *allocation,size_t size,const char *function,Type allocType,int allocSize)
{
	if (!allocation) {
		std::cerr << "out of memory during call to " << function << "(" << size << ") with " << listSize << " active allocations\n";
		exit(0);
	}
	if (listSize == listMax) {
		listMax += 1000;
		void ** newPtr  = (void **)malloc(sizeof(void *)*listMax);
		Type *  newType = (Type *) malloc(sizeof(Type)  *listMax);
		int *   newSize = (int *)  malloc(sizeof(int)   *listMax);
		if (!newPtr || !newType || !newSize) {
			std::cerr << "out of memory during call to " << function << "(" << size << ") with " << listSize << " active allocations\n";
			exit(0);
		}
		for (int i=0; i<listSize; i++) {
			newPtr[i]  = listPointer[i];
			newType[i] = listType[i];
			newSize[i] = listBytes[i];
		}
		free(listPointer);
		free(listType);
		free(listBytes);
		listPointer = newPtr;
		listType = newType;
		listBytes = newSize;
	}
	listPointer[listSize] = allocation;
	listType[listSize] = allocType;
	listBytes[listSize] = allocSize;
	listSize++;
	
	memset(allocation,0xCC,allocSize);
	return true;
}

void remove(void *p,const char *function,Type allocType)
{
	if (!p) {
		std::cerr << "attempt to " << function << " a NULL pointer with " << listSize << " active allocations\n";
		exit(0);
	}
	for (int i=0; i<listSize; i++)
		if (listPointer[i] == p) {
			if (listType[i] != allocType)
				std::cerr << "attempt to " << function << " a pointer created with a different service\n";
			listSize--;
			memset(p,0xDD,listBytes[i]);
			listPointer[i] 	= listPointer[listSize];
			listType[i] 	= listType[listSize];
			listBytes[i]	= listBytes[listSize];
			return;
		}
	std::cerr << "attempt to " << function << " a non-active pointer with " << listSize << " active allocations\n";
}

class Exit {
public:
	~Exit() { 
		if (listSize>0) {
			std::cerr << "program completed with " << listSize << " active allocations\n";
			for (int i=0; i<listSize; i++)
				std::cerr << listType[i] << ": " << listBytes[i] << "\n";
		}
	}
};
	
static Exit myExit;

void * operator new(size_t size)
{
	#ifdef MAX_ALLOC
	if (listSize > MAX_ALLOC) 
		throw new std::bad_alloc;
	#endif
	void * result = malloc(size);
	add(result,size,"new",NEW,size);
	return result;
}

void * operator new[](size_t size)
{
	#ifdef MAX_ALLOC
	if (listSize > MAX_ALLOC) 
		throw new std::bad_alloc;
	#endif
	void * result = malloc(size);
	add(result,size,"new[]",NEW_ARRAY,size);
	return result;
}

void operator delete(void *p) noexcept
{
	remove(p,"delete",NEW);
	free(p);
}

void operator delete[](void *p) noexcept
{
	remove(p,"delete[]",NEW_ARRAY);
	free(p);
}
