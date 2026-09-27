import ShowPerPage from '@/Components/ShowPerPage';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Plus, X } from 'lucide-react';

export default function PageHeader({
    data,
    perPageItem,
    handlePerPageChange,
    handleSearch,
    placeholder,
    handleReset,
    handleCreate,
    canCreate,
    CreateBtn = 'Add New',
}) {
    return (
        <div className="flex flex-wrap items-center gap-4">
            <ShowPerPage perPageItem={perPageItem} handlePerPageChange={handlePerPageChange} />
            <div className="flex w-full md:w-1/3">
                <div className="flex-1">
                    <Input type="text" value={data.search} onChange={handleSearch} className="h-10" placeholder={placeholder} name="search" />
                </div>
                <Button variant="destructive" onClick={handleReset} className="ml-2 h-10 cursor-pointer">
                    <X size={20} />
                </Button>
            </div>

            <div className="ml-auto w-full sm:w-auto">
                {canCreate && (
                    <Button onClick={handleCreate} className="w-full">
                        <Plus className="mr-0 h-4 w-4" />
                        {CreateBtn}
                    </Button>
                )}
            </div>
        </div>
    );
}
