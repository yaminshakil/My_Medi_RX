import PharmacyForm from "./Form";

type Owner = {
    id: number;
    name: string;
    email: string;
};

type Pharmacy = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    description: string | null;
    logo: string | null;
    user_id: number;
    address: Record<string, string> | null;
};

export default function Edit({
    owners,
    pharmacy,
}: {
    owners: Owner[];
    pharmacy: Pharmacy;
}) {
    return <PharmacyForm owners={owners} pharmacy={pharmacy} />;
}
