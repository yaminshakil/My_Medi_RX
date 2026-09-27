import { Button } from '@/Components/ui/button';
import { Card } from 'antd';

export default function SelectChamber({ data, setData, chambers, selectedChamber, setSelectedChamber }) {
    return (
        <div className="mb-6">
            <h3 className="mb-2 text-lg font-semibold">Select Chamber</h3>
            <div className="flex flex-wrap gap-3">
                {chambers.map((ch) => (
                    <Button
                        type="button"
                        key={ch.id}
                        onClick={() => {
                            setSelectedChamber(ch);
                            setData({
                                ...data,
                                chamber_id: ch.id,
                                appointment_date: '',
                                appointment_time: '',
                                appointment_type: 'online',
                            });
                        }}
                        className={`rounded-lg border px-4 py-2 ${selectedChamber?.id === ch.id
                                ? 'border text-white'
                                : 'border border-[var(--base-color)] bg-gray-100 text-black hover:bg-lime-100'
                            }`}
                    >
                        {ch.name}
                    </Button>
                ))}
            </div>
            {selectedChamber && (
                <Card className="mt-4" title="Address" size="small">
                    <p>{selectedChamber.address}</p>
                </Card>
            )}
        </div>
    );
}
